<?php

namespace App\Manager;

use App\Entity\Entreprise;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Factory\MessageThreadFactory;
use App\Repository\MessageThreadRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageThreadManager extends AbstractManager
{
    public function __construct(
        private MessageThreadFactory $messageThreadFactory,
        protected ManagerRegistry $managerRegistry,
        protected string $entityName = MessageThread::class
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->entityName = $entityName;
    }

    public function createOrGet(Signalement $signalement, Entreprise $entreprise)
    {
        /** @var MessageThreadRepository $messageThreadRepository */
        $messageThreadRepository = $this->managerRegistry->getRepository(MessageThread::class);
        $messageThread = $messageThreadRepository->findOneBy([
            'signalement' => $signalement,
            'entreprise' => $entreprise,
        ]);

        if (null === $messageThread) {
            return $this->messageThreadFactory->createInstanceFrom($signalement, $entreprise);
        }

        return $messageThread;
    }
}
