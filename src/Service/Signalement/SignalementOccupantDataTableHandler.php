<?php

namespace App\Service\Signalement;

use App\Dto\DataTableRequest;
use App\Dto\DataTableResponse;
use App\Dto\SignalementOccupantDataTableFilters;
use App\Entity\Enum\ProcedureProgress;
use App\Entity\Enum\Role;
use App\Entity\Enum\SignalementStatus;
use App\Manager\SignalementManager;
use App\Twig\AppExtension;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SignalementOccupantDataTableHandler
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

    public function handleRequest(DataTableRequest $dataTableRequest): DataTableResponse
    {
        $signalementOccupantDataTableFilters = $this->buildFilters($dataTableRequest);

        $signalementsTotal = $this->signalementManager->findDeclaredByOccupants();
        $countSignalementsTotal = \count($signalementsTotal);

        $signalementsFiltered = $this->signalementManager->findDeclaredByOccupants(
            filters: $signalementOccupantDataTableFilters
        );
        $countSignalementsFiltered = \count($signalementsFiltered);

        $signalementsFilteredPaginated = $this->signalementManager->findDeclaredByOccupants(
            start: $dataTableRequest->getStart(),
            length: $dataTableRequest->getLength(),
            orderColumn: $this->getOrderColumn($dataTableRequest->getOrderColumn()),
            orderDirection: $dataTableRequest->getOrderDirection(),
            filters: $signalementOccupantDataTableFilters
        );

        $signalementsFilteredFormated = [];
        foreach ($signalementsFilteredPaginated as $row) {
            $signalementsFilteredFormated[] = $this->getSignalementResponseRow($row);
        }

        return new DataTableResponse(
            draw: $dataTableRequest->getDraw(),
            recordsTotal: $countSignalementsTotal,
            recordsFiltered: $countSignalementsFiltered,
            data: $signalementsFilteredFormated,
        );
    }

    private function buildFilters(DataTableRequest $dataTableRequest): SignalementOccupantDataTableFilters
    {
        return new SignalementOccupantDataTableFilters(
            statut: $dataTableRequest->getSearchByIndex(self::COL_SEARCH_STATUT),
            zip: $this->security->isGranted(Role::ROLE_ADMIN->value)
                ? $dataTableRequest->getSearchByIndex(self::COL_SEARCH_TERRITOIRE)
                : null,
            date: $dataTableRequest->getSearchByIndex(self::COL_SEARCH_DATE),
            niveauInfestation: $dataTableRequest->getSearchByIndex(self::COL_SEARCH_NIVEAU_INFESTATION),
            adresse: $dataTableRequest->getSearchByIndex(self::COL_SEARCH_ADRESSE),
            type: $this->security->isGranted(Role::ROLE_ADMIN->value)
                ? $dataTableRequest->getSearchByIndex(self::COL_SEARCH_TYPE)
                : null,
            etatInfestation: $this->security->isGranted(Role::ROLE_ADMIN->value)
                ? $dataTableRequest->getSearchByIndex(self::COL_SEARCH_ETAT_INFESTATION)
                : null,
            motifCloture: $this->security->isGranted(Role::ROLE_ADMIN->value)
                ? $dataTableRequest->getSearchByIndex(self::COL_SEARCH_MOTIF_CLOTURE)
                : null,
        );
    }

    private function getOrderColumn(string $orderColumn): string
    {
        switch ($orderColumn) {
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

    private function getSignalementResponseRow(array $row): array
    {
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

        return $signalementFormatted;
    }

    private function formatStatut(string $statut): string
    {
        return '<p class="fr-badge fr-badge--'.SignalementStatus::from($statut)->badgeColor()
                .' fr-badge--no-icon">'.SignalementStatus::from($statut)->label().'</p>';
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
                <progress value="'.ProcedureProgress::from($procedureLabel)->percent().'" max="100"></progress>';
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
