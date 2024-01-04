<?php

namespace App\Service\Signalement;

use App\Entity\Enum\Role;
use App\Entity\Signalement;
use App\Manager\SignalementManager;
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
    ) {
    }

    public function list(Request $request): array
    {
        $requestColumns = $request->get('columns');
        $searchStatut = $requestColumns[self::COL_SEARCH_STATUT]['search']['value'];
        $searchTerritoireZip = $requestColumns[self::COL_SEARCH_TERRITOIRE]['search']['value'];

        $signalementsTotal = $this->signalementManager->findDeclaredByOccupants(
        );
        $signalementsFiltered = $this->signalementManager->findDeclaredByOccupants(
            start: null,
            length: null,
            zip: $searchTerritoireZip,
            statut: $searchStatut,
        );
        $signalementsFilteredData = $this->signalementManager->findDeclaredByOccupants(
            start: $request->get('start'),
            length: $request->get('length'),
            zip: $searchTerritoireZip,
            statut: $searchStatut,
        );

        $signalementsFilteredFormated = [];
        /** @var Signalement $signalement */
        foreach ($signalementsFilteredData as $signalement) {
            $signalementFormatted = [
                self::formatStatut($signalement, $this->security),
                $signalement->getReference(),
                $signalement->getCreatedAt()->format('d/m/Y'),
                self::formatNiveauInfestation($signalement),
                self::formatCodePostal($signalement),
            ];
            if ($this->security->isGranted(Role::ROLE_ADMIN->value)) {
                $signalementFormatted[] = self::formatTypeSignalement($signalement);
                $signalementFormatted[] = self::formatProcedure($signalement);
            }
            $signalementFormatted[] = self::formatButton($signalement, $this->urlGenerator);

            $signalementsFilteredFormated[] = $signalementFormatted;
        }

        return [
            'draw' => $request->get('draw'),
            'recordsTotal' => \count($signalementsTotal),
            'recordsFiltered' => \count($signalementsFiltered),
            'data' => $signalementsFilteredFormated,
        ];
    }

    private static function formatStatut(Signalement $signalement, Security $security): string
    {
        $statutFormat = new StatutFormat($security, $signalement);

        return '<p class="fr-badge fr-badge--'.$statutFormat->getBadgeName()
                .' fr-badge--no-icon">'
                .$statutFormat->getLabel().'</p>';
    }

    private static function formatNiveauInfestation(Signalement $signalement): string
    {
        // <td>{% include "common/components/niveau-infestation.html.twig" %}</td>
        return 'TODO niv inf';
    }

    private static function formatCodePostal(Signalement $signalement): string
    {
        return $signalement->getCodePostal().' '.$signalement->getVille();
    }

    private static function formatTypeSignalement(Signalement $signalement): string
    {
        if ($signalement->isLogementSocial()) {
            return 'Logement social';
        }

        if ($signalement->isAutotraitement()) {
            return 'Auto-traitement';
        }

        return 'A traiter';
    }

    private static function formatProcedure(Signalement $signalement): string
    {
        // <td>{% include "common/components/signalement-procedure.html.twig" %}</td>
        return 'TODO procedure';
    }

    private static function formatButton(Signalement $signalement, UrlGeneratorInterface $urlGenerator): string
    {
        $link = $urlGenerator->generate('app_signalement_view', ['uuid' => $signalement->getUuid()]);

        return '<span class="button-view">
                <a class="fr-btn fr-icon-arrow-right-fill"
                href="'.$link.'"
                title="Voir le signalement '.$signalement->getReference().'"
                ></a></span>';
    }
}
