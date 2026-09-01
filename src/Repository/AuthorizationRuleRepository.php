<?php

namespace App\Repository;

use App\Entity\AccessPoint;
use App\Entity\AuthorizationRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<AuthorizationRule>
 *
 * @method AuthorizationRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthorizationRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthorizationRule[]    findAll()
 * @method AuthorizationRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorizationRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthorizationRule::class);
    }

    public function add(AuthorizationRule $authorizationRule): void
    {
        $this->getEntityManager()->persist($authorizationRule);
    }
    public function save(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(AuthorizationRule $authorizationRule): void
    {
        // Eliminamos los Access Log asociados a la restricción
        $this->getEntityManager()
            ->createQuery('DELETE FROM App\Entity\AccessLog al WHERE al.grantedBy = :ar')
            ->setParameter('ar', $authorizationRule)
            ->execute();

        $this->getEntityManager()->remove($authorizationRule);
    }

    /*

        Este método hace que, cuando se accede a él, se marcan como inactivas
        todas las instancias temporales de AuthorizationRule las cuales ya hayan expirado

    */
    /**
     * @throws Exception
     */
    public function checkAndMarkExpired(): void
    {
        $timezone = new \DateTimeZone('Europe/Madrid');
        $now = new \DateTimeImmutable('now', $timezone);

        $rules = $this->createQueryBuilder('ar')
            ->leftJoin('ar.accessPoint', 'ap')
            ->addSelect('ap')
            ->leftJoin('ar.user', 'u')
            ->addSelect('u')
            ->leftJoin('ar.userGroup', 'ug')
            ->addSelect('ug')
            ->where('ar.active = 1')
            ->andWhere('ar.temp = 1')
            ->getQuery()
            ->getResult();

        foreach ($rules as $rule) {
            if ($rule->getEndTimestamp() !== null) {
                if ($rule->getEndTimestamp()->format('H:i:s') < $now) {
                    $rule->setActive(false);
                }
            }

            $this->save();
        }
    }

    public function findAllByAccessPoint(AccessPoint $accessPoint)
    {
        return $this->createQueryBuilder('ar')
            ->leftJoin('ar.accessPoint', 'ap')
            ->where('ar.accessPoint = :ap AND ar.active = true')
            ->setParameter('ap', $accessPoint)
            ->getQuery()
            ->getResult();
    }

    // Creamos un método para ver si un usuario tiene acceso o no

    /**
     * @throws Exception
     */
    public function userHasAccess(int $user, int $accessPoint, string $weekday): array
    {
        $qb = $this->createQueryBuilder('ar')
            ->leftJoin('ar.accessPoint', 'ap')
            ->addSelect('ap')
            ->andWhere('ar.user = :user')
            ->andWhere('ar.accessPoint = :accessPoint')
            ->andWhere('ar.active = 1')
            ->andWhere('ap.active = 1')
            ->setParameter('user', $user)
            ->setParameter('accessPoint', $accessPoint);

        switch ($weekday) {
            case 'mon':
                $qb->andWhere('ar.mon = true');
                break;
            case 'tue':
                $qb->andWhere('ar.tue = true');
                break;
            case 'wed':
                $qb->andWhere('ar.wed = true');
                break;
            case 'thu':
                $qb->andWhere('ar.thu = true');
                break;
            case 'fri':
                $qb->andWhere('ar.fri = true');
                break;
            case 'sat':
                $qb->andWhere('ar.sat = true');
                break;
            case 'sun':
                $qb->andWhere('ar.sun = true');
                break;
        }

        $rules = $qb->getQuery()->getResult();

        $now = (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid')))->format('H:i:s');

        $validRules = [];

        foreach ($rules as $rule) {
            // Una regla sin horario está disponible todo el día, así que vale a
            // cualquier hora. Mismo criterio que userHasAccessByGroup().
            if ($rule->getStartTimestamp() !== null && $rule->getEndTimestamp() !== null) {
                $start = $rule->getStartTimestamp()->format('H:i:s');
                $end = $rule->getEndTimestamp()->format('H:i:s');

                if ($now >= $start && $now <= $end) {
                    $validRules[] = $rule;
                }
            } else {
                $validRules[] = $rule;
            }
        }

        return $validRules;
    }

    /**
     * @throws Exception
     */
    public function userHasAccessByGroup(int $group, int $accessPoint, string $weekday): array
    {
        $qb = $this->createQueryBuilder('ar')
            ->where('ar.userGroup = :group')
            ->andWhere('ar.accessPoint = :accessPoint')
            ->andWhere('ar.active = true')
            ->setParameter('group', $group)
            ->setParameter('accessPoint', $accessPoint);

        switch ($weekday) {
            case 'mon':
                $qb->andWhere('ar.mon = true');
                break;
            case 'tue':
                $qb->andWhere('ar.tue = true');
                break;
            case 'wed':
                $qb->andWhere('ar.wed = true');
                break;
            case 'thu':
                $qb->andWhere('ar.thu = true');
                break;
            case 'fri':
                $qb->andWhere('ar.fri = true');
                break;
            case 'sat':
                $qb->andWhere('ar.sat = true');
                break;
            case 'sun':
                $qb->andWhere('ar.sun = true');
                break;
        }

        $rules = $qb->getQuery()->getResult();

        $now = (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid')))->format('H:i:s');
        $validRules = [];

        foreach ($rules as $rule) {
            if ($rule->getStartTimestamp() !== null && $rule->getEndTimestamp() !== null) {
                $start = $rule->getStartTimestamp()->format('H:i:s');
                $end = $rule->getEndTimestamp()->format('H:i:s');

                if ($now >= $start && $now <= $end) {
                    $validRules[] = $rule;
                }
            } else {
                $validRules[] = $rule;
            }
        }

        return $validRules;
    }
}