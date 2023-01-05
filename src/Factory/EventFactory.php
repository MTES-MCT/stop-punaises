<?php

namespace App\Factory;

use App\Entity\Event;

class EventFactory
{
    public function createInstance(
        string $domain,
        string $title,
        string $description,
        ?int $userId = null,
        ?string $recipient = null,
        ?string $label = null,
        ?string $actionLink = null,
        ?string $actionLabel = null,
        ?string $entityName = null,
        ?string $entityUuid = null
    ) {
        return (new Event())
            ->setActive(true)
            ->setDomain($domain)
            ->setTitle($title)
            ->setDescription($description)
            ->setUserId($userId)
            ->setRecipient($recipient)
            ->setLabel($label ?? null)
            ->setActionLabel($actionLabel ?? null)
            ->setActionLink($actionLink ?? null)
            ->setEntityName($entityName)
            ->setEntityUuid($entityUuid);
    }
}
