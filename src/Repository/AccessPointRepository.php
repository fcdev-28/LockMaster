<?php

namespace App\Repository;

use App\Entity\AccessPoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccessPoint>
 *
 * @method AccessPoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessPoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessPoint[]    findAll()
 * @method AccessPoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessPoint::class);
    }

    public function add(AccessPoint $accessPoint): void
    {
        $this->getEntityManager()->persist($accessPoint);
    }
    public function save(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(AccessPoint $accessPoint): void
    {
        // Eliminamos los Access Log del punto de acceso que queremos eliminar
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AccessLog al WHERE al.accessPoint = :ap')
            ->setParameter('ap', $accessPoint)
            ->execute();

        // Eliminamos los Access Log de las Authorization Rule que están vinculadas al punto de acceso deseado
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AccessLog al WHERE al.grantedBy IN (
                SELECT ar.id FROM App\Entity\AuthorizationRule ar WHERE ar.accessPoint = :ap
            )')
            ->setParameter('ap', $accessPoint)
            ->execute();

        // Eliminamos las Authorization Rule asociadas al punto de acceso deseado
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AuthorizationRule ar WHERE ar.accessPoint = :ap')
            ->setParameter('ap', $accessPoint)
            ->execute();

        // Eliminamos el punto de acceso que queremos
        $this->getEntityManager()->remove($accessPoint);
        $this->getEntityManager()->flush();
    }
}
