<?php

namespace App\Service\Signalement;

use App\Entity\Enum\ProcedureProgress;
use App\Entity\Enum\Role;
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
            $searchTerritoireZip = $requestColumns[self::COL_SEARCH_TERRITOIRE]['search']['value'];
            $searchType = $requestColumns[self::COL_SEARCH_TYPE]['search']['value'];
            $searchEtatInfestation = $requestColumns[self::COL_SEARCH_ETAT_INFESTATION]['search']['value'];
            $searchMotifCloture = $requestColumns[self::COL_SEARCH_MOTIF_CLOTURE]['search']['value'];
        }
        $searchStatut = $requestColumns[self::COL_SEARCH_STATUT]['search']['value'];
        $searchDate = $requestColumns[self::COL_SEARCH_DATE]['search']['value'];
        $searchNiveauInfestation = $requestColumns[self::COL_SEARCH_NIVEAU_INFESTATION]['search']['value'];
        $searchAdresse = $requestColumns[self::COL_SEARCH_ADRESSE]['search']['value'];

        $requestOrder = $request->get('order');
        $orderColumn = $this->getOrderColumn($requestOrder);
        $orderDirection = $requestOrder[0]['dir'];

        $signalementsTotal = $this->signalementManager->findDeclaredByOccupants();
        $countSignalementsTotal = \count($signalementsTotal);

        $signalementsFiltered = $this->signalementManager->findDeclaredByOccupants(
            statut: $searchStatut,
            zip: $searchTerritoireZip,
            date: $searchDate,
            niveauInfestation: $searchNiveauInfestation,
            adresse: $searchAdresse,
            type: $searchType,
            etatInfestation: $searchEtatInfestation,
            motifCloture: $searchMotifCloture,
        );
        $countSignalementsFiltered = \count($signalementsFiltered);

        $signalementsFilteredPaginated = $this->signalementManager->findDeclaredByOccupants(
            start: $request->get('start'),
            length: $request->get('length'),
            orderColumn: $orderColumn,
            orderDirection: $orderDirection,
            statut: $searchStatut,
            zip: $searchTerritoireZip,
            date: $searchDate,
            niveauInfestation: $searchNiveauInfestation,
            adresse: $searchAdresse,
            type: $searchType,
            etatInfestation: $searchEtatInfestation,
            motifCloture: $searchMotifCloture,
        );

        $signalementsFilteredFormated = [];
        foreach ($signalementsFilteredPaginated as $row) {
            $createdAt = new \DateTime($row['created_at']);
            $signalementFormatted = [
                $this->formatStatut($row['statut']),
                $row['reference'],
                $createdAt->format('d/m/Y'),
                $this->formatNiveauInfestation($row['niveau_infestation']),
                $this->formatCodePostal($row),
            ];
            if ($this->security->isGranted(Role::ROLE_ADMIN->value)) {
                $signalementFormatted[] = $this->formatTypeSignalement($row);
                $signalementFormatted[] = $this->formatProcedure($row['procedure_progress']);
            }
            $signalementFormatted[] = $this->formatButton($row);

            $signalementsFilteredFormated[] = $signalementFormatted;
        }

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

    private function formatStatut(string $statut): string
    {
        return '<p class="fr-badge fr-badge--'.StatutFormat::getBadgeNameByLabel($statut)
                .' fr-badge--no-icon">'.$statut.'</p>';
    }

    private function formatNiveauInfestation(string $niveauInfestation): string
    {
        return '<span class="niveau-infestation niveau-'.$niveauInfestation.'">'
                .$this->appExtension->formatLabelInfestation($niveauInfestation).
                '</span>';
    }

    private function formatCodePostal(array $row): string
    {
        return $row['code_postal'].' '.$row['ville'];
    }

    private function formatTypeSignalement(array $row): string
    {
        if ($row['logement_social']) {
            return 'Logement social';
        }

        if ($row['autotraitement']) {
            return 'Auto-traitement';
        }

        return 'A traiter';
    }

    private function formatProcedure(string $procedureLabel): string
    {
        return '<label>'.ProcedureProgress::from($procedureLabel)->label().'</label>
                <br>
                <progress value="'.ProcedureProgress::from($procedureLabel)->value.'" max="100"></progress>';
    }

    private function formatButton(array $row): string
    {
        $link = $this->urlGenerator->generate('app_signalement_view', ['uuid' => $row['uuid']]);

        return '<span class="button-view">
                <a class="fr-btn fr-icon-arrow-right-fill"
                href="'.$link.'"
                title="Voir le signalement '.$row['reference'].'"
                ></a></span>';
    }
}
