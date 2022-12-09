<?php

namespace App\EventSubscriber;

use App\Entity\Message;
use App\Service\Mailer\MailerProvider;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

class MessageAddedSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerProvider $mailerProvider)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Message) {
                // send message
            }
        }
    }
}
