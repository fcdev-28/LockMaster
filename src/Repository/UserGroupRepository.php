<?php

namespace App\Repository;

use App\Entity\UserGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserGroup>
 *
 * @method UserGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGroup[]    findAll()
 * @method UserGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroup::class);
    }

    public function add(UserGroup $userGroup): void
    {
        $this->getEntityManager()->persist($userGroup);
    }

    public function save(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(UserGroup $userGroup): void
    {
        // Eliminamos las Authorization Rule asociadas al grupo deseado
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AuthorizationRule ar WHERE ar.userGroup = :userGroup')
            ->setParameter('userGroup', $userGroup)
            ->execute();

        // Eliminamos el grupo que queremos
        $this->getEntityManager()->remove($userGroup);
        $this->getEntityManager()->flush();
    }
}
