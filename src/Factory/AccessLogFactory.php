<?php

namespace App\Factory;

use App\Entity\AccessLog;
use App\Repository\AccessLogRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<AccessLog>
 *
 * @method        AccessLog|Proxy                              create(array|callable $attributes = [])
 * @method static AccessLog|Proxy                              createOne(array $attributes = [])
 * @method static AccessLog|Proxy                              find(object|array|mixed $criteria)
 * @method static AccessLog|Proxy                              findOrCreate(array $attributes)
 * @method static AccessLog|Proxy                              first(string $sortedField = 'id')
 * @method static AccessLog|Proxy                              last(string $sortedField = 'id')
 * @method static AccessLog|Proxy                              random(array $attributes = [])
 * @method static AccessLog|Proxy                              randomOrCreate(array $attributes = [])
 * @method static AccessLogRepository|ProxyRepositoryDecorator repository()
 * @method static AccessLog[]|Proxy[]                          all()
 * @method static AccessLog[]|Proxy[]                          createMany(int $number, array|callable $attributes = [])
 * @method static AccessLog[]|Proxy[]                          createSequence(iterable|callable $sequence)
 * @method static AccessLog[]|Proxy[]                          findBy(array $attributes)
 * @method static AccessLog[]|Proxy[]                          randomRange(int $min, int $max, array $attributes = [])
 * @method static AccessLog[]|Proxy[]                          randomSet(int $number, array $attributes = [])
 */
final class AccessLogFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return AccessLog::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $granted = self::faker()->boolean();
        $rule = null;

        if ($granted) {
            $rule = AuthorizationRuleFactory::random();
        }

        /*
            Accesos de los últimos tres meses en horario de jornada, en vez de
            repartidos por años enteros a cualquier hora de la madrugada.
        */
        $timestamp = \DateTimeImmutable::createFromMutable(
            self::faker()->dateTimeBetween('-3 months', 'now')
        )->setTime(
            self::faker()->numberBetween(7, 18),
            self::faker()->numberBetween(0, 59),
            self::faker()->numberBetween(0, 59)
        );

        // Si cae en sábado o domingo lo movemos al viernes anterior
        $weekday = (int) $timestamp->format('N');
        if ($weekday > 5) {
            $timestamp = $timestamp->modify('-' . ($weekday - 5) . ' days');
        }

        return [
            'accessPoint' => AccessPointFactory::random(),
            'granted' => $granted,
            'grantedBy' => $rule,
            'timestamp' => $timestamp,
            'user' => UserFactory::random(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(AccessLog $accessLog): void {})
        ;
    }
}
