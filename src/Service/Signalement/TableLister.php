<?php

namespace App\Service\Signalement;

use App\Entity\Enum\Role;
use App\Entity\Signalement;
use App\Manager\SignalementManager;
use App\Twig\AppExtension;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TableLister
{
    private const COL_SEARCH_STATUT = 0;
    private const COL_SEARCH_TERRITOIRE = 1;
    private const COL_SEARCH_DATE = 2;
    private const COL_SEARCH_NIVEAU_INFESTATION = 3;
    private const COL_SEARCH_ADRESSE = 4;
    private const COL_SEARCH_TYPE = 5;
    private const COL_SEARCH_ETAT_INFESTATION = 6;
    private const COL_SEARCH_MOTIF_CLOTURE = 7;

    private const ORDER_COL_STATUT = 0;
    private const ORDER_COL_ID = 1;
    private const ORDER_COL_DATE = 2;
    private const ORDER_COL_NIVEAU_INFESTATION = 3;
    private const ORDER_COL_COMMUNE = 4;
    private const ORDER_COL_TYPE = 5;
    private const ORDER_COL_PROCEDURE = 6;

    public function __construct(
        private SignalementManager $signalementManager,
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private AppExtension $appExtension,
    ) {
    }

    public function list(Request $request): array
    {
        $requestColumns = $request->get('columns');

        $searchStatut = null;
        $searchTerritoireZip = null;
        $searchType = null;
        $searchEtatInfestation = null;
        $searchMotifCloture = null;
        if ($this->security->isGranted(Role::ROLE_ADMIN->value)) {
            // TODO : $searchStatut = $requestColumns[self::COL_SEARCH_STATUT]['search']['value'];
            $searchTerritoireZip = $requestColumns[self::COL_SEARCH_TERRITOIRE]['search']['value'];
            $searchType = $requestColumns[self::COL_SEARCH_TYPE]['search']['value'];
            $searchEtatInfestation = $requestColumns[self::COL_SEARCH_ETAT_INFESTATION]['search']['value'];
            $searchMotifCloture = $requestColumns[self::COL_SEARCH_MOTIF_CLOTURE]['search']['value'];
        }
        $searchDate = $requestColumns[self::COL_SEARCH_DATE]['search']['value'];
        $searchNiveauInfestation = $requestColumns[self::COL_SEARCH_NIVEAU_INFESTATION]['search']['value'];
        $searchAdresse = $requestColumns[self::COL_SEARCH_ADRESSE]['search']['value'];

        $requestOrder = $request->get('order');
        $orderColumn = $this->getOrderColumn($requestOrder);
        $orderDirection = $requestOrder[0]['dir'];

        $countSignalementsTotal = $this->signalementManager->findDeclaredByOccupants(
            returnCount: true,
        );
        $countSignalementsFiltered = $this->signalementManager->findDeclaredByOccupants(
            returnCount: true,
            start: null,
            length: null,
            zip: $searchTerritoireZip,
            statut: null,
            date: $searchDate,
            niveauInfestation: $searchNiveauInfestation,
            adresse: $searchAdresse,
            type: $searchType,
            etatInfestation: $searchEtatInfestation,
            motifCloture: $searchMotifCloture,
        );
        $signalementsFilteredData = $this->signalementManager->findDeclaredByOccupants(
            returnCount: false,
            start: $request->get('start'),
            length: $request->get('length'),
            orderColumn: $orderColumn,
            orderDirection: $orderDirection,
            zip: $searchTerritoireZip,
            statut: null,
            date: $searchDate,
            niveauInfestation: $searchNiveauInfestation,
            adresse: $searchAdresse,
            type: $searchType,
            etatInfestation: $searchEtatInfestation,
            motifCloture: $searchMotifCloture,
        );

        $signalementsFilteredFormated = [];
        /** @var Signalement $signalement */
        foreach ($signalementsFilteredData as $signalement) {
            $signalementFormatted = [
                $this->formatStatut($signalement),
                $signalement->getReference(),
                $signalement->getCreatedAt()->format('d/m/Y'),
                $this->formatNiveauInfestation($signalement),
                $this->formatCodePostal($signalement),
            ];
            if ($this->security->isGranted(Role::ROLE_ADMIN->value)) {
                $signalementFormatted[] = $this->formatTypeSignalement($signalement);
                $signalementFormatted[] = $this->formatProcedure($signalement);
            }
            $signalementFormatted[] = $this->formatButton($signalement);

            $signalementsFilteredFormated[] = $signalementFormatted;
        }

        $signalementsFilteredFormated = $this->checkIfReorder($signalementsFilteredFormated, $orderColumn, $orderDirection);

        return [
            'draw' => $request->get('draw'),
            'recordsTotal' => $countSignalementsTotal,
            'recordsFiltered' => $countSignalementsFiltered,
            'data' => $signalementsFilteredFormated,
        ];
    }

    private function getOrderColumn(array $requestOrder): string
    {
        $orderColRequest = $requestOrder[0]['column'];
        switch ($orderColRequest) {
            case self::ORDER_COL_STATUT:
                return 'statut';
                break;
            case self::ORDER_COL_ID:
                return 'id';
                break;
            case self::ORDER_COL_DATE:
                return 'date';
                break;
            case self::ORDER_COL_NIVEAU_INFESTATION:
                return 'infestation';
                break;
            case self::ORDER_COL_COMMUNE:
                return 'commune';
                break;
            case self::ORDER_COL_TYPE:
                return 'type';
                break;
            case self::ORDER_COL_PROCEDURE:
                return 'procedure';
                break;
            default:
                return '';
                break;
        }
    }

    private function checkIfReorder(array $signalementsFilteredFormated, string $orderColumn, string $orderDirection): array
    {
        if ('procedure' != $orderColumn && 'statut' != $orderColumn) {
            return $signalementsFilteredFormated;
        }

        if ('procedure' == $orderColumn) {
            $colIndex = self::ORDER_COL_PROCEDURE;
        } elseif ('statut' == $orderColumn) {
            $colIndex = self::ORDER_COL_STATUT;
        }

        usort(
            $signalementsFilteredFormated,
            fn ($a, $b) => $a[$colIndex] <=> $b[$colIndex]
        );
        if ('desc' == $orderDirection) {
            $signalementsFilteredFormated = array_reverse($signalementsFilteredFormated);
        }

        return $signalementsFilteredFormated;
    }

    private function formatStatut(Signalement $signalement): string
    {
        $statutFormat = new StatutFormat($this->security, $signalement);

        return '<p class="fr-badge fr-badge--'.$statutFormat->getBadgeName()
                .' fr-badge--no-icon">'
                .$statutFormat->getLabel().'</p>';
    }

    private function formatNiveauInfestation(Signalement $signalement): string
    {
        return '<span class="niveau-infestation niveau-'.$signalement->getNiveauInfestation().'">'
                .$this->appExtension->formatLabelInfestation($signalement->getNiveauInfestation()).
                '</span>';
    }

    private function formatCodePostal(Signalement $signalement): string
    {
        return $signalement->getCodePostal().' '.$signalement->getVille();
    }

    private function formatTypeSignalement(Signalement $signalement): string
    {
        if ($signalement->isLogementSocial()) {
            return 'Logement social';
        }

        if ($signalement->isAutotraitement()) {
            return 'Auto-traitement';
        }

        return 'A traiter';
    }

    private function formatProcedure(Signalement $signalement): string
    {
        $procedureFormat = new ProcedureFormat($signalement);

        return '<label>'.$procedureFormat->getLabel().'</label>
                <br>
                <progress value="'.$procedureFormat->getPercent().'" max="100"></progress>';
    }

    private function formatButton(Signalement $signalement): string
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);

        return '<span class="button-view">
                <a class="fr-btn fr-icon-arrow-right-fill"
                href="'.$link.'"
                title="Voir le signalement '.$signalement->getReference().'"
                ></a></span>';
    }
}
