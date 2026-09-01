<?php

namespace App\Security\Voter;

use App\Entity\AuthorizationRule;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AuthorizationRuleVoter extends Voter
{
    const CREATE = 'AUTH_CREATE';
    const DELETE = 'AUTH_DELETE';

    private Security $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [
            self::CREATE,
            self::DELETE
        ], true)) {
            return false;
        }

        // Para crear no necesitamos un sujeto, porque estamos creando
        if ($attribute === self::CREATE && $subject === null) {
            return true;
        }

        // Los demás sí esperan un objeto Authorization Rule (eliminar)
        return $subject instanceof AuthorizationRule;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($user);

            case self::DELETE:
                return $this->canDelete($user, $subject);
        }

        return false;
    }

    private function canCreate(User $user): bool
    {
        // Sólo podrá crear restricciones un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(User $user, AuthorizationRule $subject): bool
    {
        // Sólo podrá eliminar restricciones un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }
}