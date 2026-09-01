<?php

namespace App\Security\Voter;

use App\Entity\AccessPoint;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AccessPointVoter extends Voter
{
    const LIST = 'ACCESS_POINT_LIST';
    const EDIT = 'ACCESS_POINT_EDIT';
    const CREATE = 'ACCESS_POINT_CREATE';
    const DELETE = 'ACCESS_POINT_DELETE';

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

        // Para listar y crear no necesitamos un sujeto
        if (($attribute === self::CREATE || $attribute === self::LIST) && $subject === null) {
            return true;
        }

        // Los demás sí esperan un objeto AccessPoint (editar y eliminar)
        return $subject instanceof AccessPoint;
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
        // Los puntos de acceso sólo podrán ser listados por un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canEdit(User $user, AccessPoint $subject): bool
    {
        // Los puntos de acceso sólo podrán ser editados por un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canCreate(User $user): bool
    {
        // Sólo podrá crear puntos de acceso un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(User $user, AccessPoint $subject): bool
    {
        // Sólo podrá eliminar puntos de acceso un administrador
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
