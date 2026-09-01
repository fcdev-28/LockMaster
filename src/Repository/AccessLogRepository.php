<?php

namespace App\Repository;

use App\Entity\AccessLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\This;

/**
 * @extends ServiceEntityRepository<AccessLog>
 *
 * @method AccessLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessLog[]    findAll()
 * @method AccessLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessLog::class);
    }

    public function remove(AccessLog $accessLog): void
    {
        // Eliminamos la instancia que queremos
        $this->getEntityManager()->remove($accessLog);
        $this->getEntityManager()->flush();
    }

    // Método para limpiar el historial
    public function clear()
    {
        return $this->createQueryBuilder('al')
            ->delete()
            ->getQuery()
            ->execute();
    }

    public function clearByUser(User $user)
    {
        return $this->createQueryBuilder('al')
            ->delete()
            ->where('al.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function save(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findAllOrderByTimestamp(): QueryBuilder
    {
        return $this->createQueryBuilder('al')
            ->leftJoin('al.user', 'u')
            ->addSelect('u')
            ->leftJoin('al.accessPoint', 'ap')
            ->addSelect('ap')
            ->leftJoin('al.grantedBy', 'ar')
            ->addSelect('ar')
            ->orderBy('al.timestamp', 'DESC');
    }

    public function findByUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('al')
            ->leftJoin('al.user', 'u')
            ->addSelect('u')
            ->leftJoin('al.accessPoint', 'ap')
            ->addSelect('ap')
            ->leftJoin('al.grantedBy', 'ar')
            ->addSelect('ar')
            ->where('al.user = :user')
            ->setParameter('user', $user)
            ->orderBy('al.timestamp', 'DESC');
    }
}
