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
        // TODO : $searchStatut = $requestColumns[self::COL_SEARCH_STATUT]['search']['value'];
        $searchTerritoireZip = $requestColumns[self::COL_SEARCH_TERRITOIRE]['search']['value'];
        $searchDate = $requestColumns[self::COL_SEARCH_DATE]['search']['value'];
        $searchNiveauInfestation = $requestColumns[self::COL_SEARCH_NIVEAU_INFESTATION]['search']['value'];
        $searchAdresse = $requestColumns[self::COL_SEARCH_ADRESSE]['search']['value'];
        $searchType = $requestColumns[self::COL_SEARCH_TYPE]['search']['value'];
        $searchEtatInfestation = $requestColumns[self::COL_SEARCH_ETAT_INFESTATION]['search']['value'];
        $searchMotifCloture = $requestColumns[self::COL_SEARCH_MOTIF_CLOTURE]['search']['value'];

        $signalementsTotal = $this->signalementManager->findDeclaredByOccupants(
        );
        $signalementsFiltered = $this->signalementManager->findDeclaredByOccupants(
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
            start: $request->get('start'),
            length: $request->get('length'),
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

        return [
            'draw' => $request->get('draw'),
            'recordsTotal' => \count($signalementsTotal),
            'recordsFiltered' => \count($signalementsFiltered),
            'data' => $signalementsFilteredFormated,
        ];
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
