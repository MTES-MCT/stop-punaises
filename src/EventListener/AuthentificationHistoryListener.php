<?php

namespace App\EventListener;

use App\Entity\Enum\HistoryEntryEvent;
use App\Entity\User;
use App\Manager\HistoryEntryManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthentificationHistoryListener
{
    public function __construct(
        private readonly HistoryEntryManager $historyEntryManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        $this->createAuthentificationHistory(HistoryEntryEvent::LOGIN, $user);
    }

    private function createAuthentificationHistory(HistoryEntryEvent $historyEntryEvent, User $user): void
    {
        try {
            $historyEntry = $this->historyEntryManager->create(
                historyEntryEvent: $historyEntryEvent,
                entityHistory: $user,
                flush: false
            );

            $source = $this->historyEntryManager->getSource();
            $historyEntry->setSource($source);
            $this->historyEntryManager->save($historyEntry);

            return;
        } catch (\Throwable $exception) {
            $this->logger->error(\sprintf(
                'Failed to create login history entry (%s) on user : %d',
                $exception->getMessage(),
                $user->getId()
            ));
        }
    }
}
