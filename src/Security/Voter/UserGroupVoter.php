<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\UserGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserGroupVoter extends Voter
{
    const LIST = 'GROUP_LIST';
    const EDIT = 'GROUP_EDIT';
    const CREATE = 'GROUP_CREATE';
    const DELETE = 'GROUP_DELETE';

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
            self::LIST,
            self::EDIT,
            self::CREATE,
            self::DELETE
        ], true)) {
            return false;
        }

        // GROUP_CREATE no necesita un sujeto, porque estamos creando
        if (($attribute === self::CREATE || $attribute || self::LIST) && $subject === null) {
            return true;
        }

        // Los demás sí esperan un objeto UserGroup (editar y eliminar)
        return $subject instanceof UserGroup;
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
            case self::LIST:
                return $this->canList($user);

            case self::EDIT:
                return $this->canEdit($user, $subject);

            case self::CREATE:
                return $this->canCreate($user);

            case self::DELETE:
                return $this->canDelete($user, $subject);
        }

        return false;
    }

    private function canList(User $user): bool
    {
        // Los grupos sólo podrán ser listados por un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canEdit(User $user, UserGroup $subject): bool
    {
        // Los grupos sólo podrán ser editados por un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canCreate(User $user): bool
    {
        // Sólo podrá crear grupos un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(User $user, UserGroup $subject): bool
    {
        // Sólo podrá eliminar grupos un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }
}