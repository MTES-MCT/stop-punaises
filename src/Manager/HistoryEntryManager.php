<?php

namespace App\Manager;

use App\Dto\Command\CommandContext;
use App\Entity\Behaviour\EntityHistoryInterface;
use App\Entity\Enum\HistoryEntryEvent;
use App\Entity\HistoryEntry;
use App\Factory\HistoryEntryFactory;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class HistoryEntryManager extends AbstractManager
{
    public function __construct(
        private readonly HistoryEntryFactory $historyEntryFactory,
        private readonly RequestStack $requestStack,
        private readonly CommandContext $commandContext,
        ManagerRegistry $managerRegistry,
        string $entityName = HistoryEntry::class,
    ) {
        parent::__construct($managerRegistry, $entityName);
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(
        HistoryEntryEvent $historyEntryEvent,
        EntityHistoryInterface|Collection $entityHistory,
        array $changes = [],
        bool $flush = true,
    ): ?HistoryEntry {
        $historyEntry = $this->historyEntryFactory->createInstanceFrom(
            historyEntryEvent: $historyEntryEvent,
            entityHistory: $entityHistory,
        );

        $source = $this->getSource();
        $historyEntry
            ->setChanges($changes)
            ->setSource($source);

        $this->save($historyEntry, $flush);

        return $historyEntry;
    }

    /**
     * @throws ExceptionInterface
     */
    public function getSource(
    ): ?string {
        return $this->requestStack->getCurrentRequest()?->getPathInfo() ?? $this->commandContext->getCommandName();
    }
}
