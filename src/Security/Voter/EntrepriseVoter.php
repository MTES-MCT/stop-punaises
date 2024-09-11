<?php

namespace App\Security\Voter;

use App\Entity\Entreprise;
use App\Entity\Enum\Role;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EntrepriseVoter extends Voter
{
    public const EDIT = 'ENTREPRISE_EDIT';
    public const VIEW = 'ENTREPRISE_VIEW';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Entreprise;
    }

    protected function voteOnAttribute(string $attribute, $entreprise, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted(Role::ROLE_ADMIN->value)) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($entreprise, $user),
            self::EDIT => $this->canEdit($entreprise, $user),
            default => false,
        };
    }

    private function canEdit(Entreprise $entreprise, User $user): bool
    {
        return $this->canView($entreprise, $user);
    }

    private function canView(Entreprise $entreprise, User $user): bool
    {
        return $entreprise->getUser() === $user;
    }
}
