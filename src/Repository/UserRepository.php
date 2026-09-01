<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $user): void
    {
        $this->getEntityManager()->persist($user);
    }

    public function save(): void
    {
        $this->getEntityManager()->flush();
    }

    // Como en la base de datos no tenemos relaciones bidireccionales, no hemos podido definir el orphanRemoval
    // así que, debemos eliminar las instancias que quedan sueltas de las demás tablas a mano.
    public function remove(User $user): void
    {
        // Eliminamos los Access Log del usuario que queremos eliminar
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AccessLog al WHERE al.user = :user')
            ->setParameter('user', $user)
            ->execute();

        // Eliminamos los Access Log de las Authorization Rule que están vinculadas al usuario deseado
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AccessLog al WHERE al.grantedBy IN (
                SELECT ar.id FROM App\Entity\AuthorizationRule ar WHERE ar.user = :user
            )')
            ->setParameter('user', $user)
            ->execute();

        // Eliminamos las Authorization Rule asociadas al usuario deseado
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AuthorizationRule ar WHERE ar.user = :user')
            ->setParameter('user', $user)
            ->execute();

        // Eliminamos el usuario que queremos
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
