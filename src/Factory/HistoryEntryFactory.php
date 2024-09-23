<?php

namespace App\Factory;

use App\Entity\Behaviour\EntityHistoryInterface;
use App\Entity\Enum\HistoryEntryEvent;
use App\Entity\HistoryEntry;
use Symfony\Bundle\SecurityBundle\Security;

readonly class HistoryEntryFactory
{
    public function __construct(private Security $security)
    {
    }

    public function createInstanceFrom(
        HistoryEntryEvent $historyEntryEvent,
        EntityHistoryInterface $entityHistory,
    ): HistoryEntry {
        return (new HistoryEntry())
            ->setEvent($historyEntryEvent)
            ->setEntityId($entityHistory->getId())
            ->setEntityName(str_replace('Proxies\\__CG__\\', '', $entityHistory::class))
            ->setUser($this->security->getUser());
    }
}
