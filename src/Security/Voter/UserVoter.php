<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    const LIST = 'USER_LIST';
    const EDIT = 'USER_EDIT';
    const CREATE = 'USER_CREATE';
    const DELETE = 'USER_DELETE';

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

        // USER_CREATE no necesita un sujeto, porque estamos creando
        if ($attribute === self::CREATE && $subject === null) {
            return true;
        }

        // Los demás sí esperan un objeto User (editar y eliminar)
        return $subject instanceof User;
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
        // Todos los usuarios registrados pueden listar los usuarios
        return true;
    }

    private function canEdit(User $user, User $subject): bool
    {
        // Un usuario sólo podrá editarse a si mismo
        if ($user->getId() === $subject->getId()) {
            return true;
        }

        // O si es un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canCreate(User $user): bool
    {
        // Sólo podrá crear usuarios un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(User $user, User $subject): bool
    {
        // Sólo podrá eliminar usuarios un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }
}