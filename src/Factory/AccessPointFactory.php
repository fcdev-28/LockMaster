<?php

namespace App\Factory;

use App\Entity\AccessPoint;
use App\Repository\AccessPointRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<AccessPoint>
 *
 * @method        AccessPoint|Proxy                              create(array|callable $attributes = [])
 * @method static AccessPoint|Proxy                              createOne(array $attributes = [])
 * @method static AccessPoint|Proxy                              find(object|array|mixed $criteria)
 * @method static AccessPoint|Proxy                              findOrCreate(array $attributes)
 * @method static AccessPoint|Proxy                              first(string $sortedField = 'id')
 * @method static AccessPoint|Proxy                              last(string $sortedField = 'id')
 * @method static AccessPoint|Proxy                              random(array $attributes = [])
 * @method static AccessPoint|Proxy                              randomOrCreate(array $attributes = [])
 * @method static AccessPointRepository|ProxyRepositoryDecorator repository()
 * @method static AccessPoint[]|Proxy[]                          all()
 * @method static AccessPoint[]|Proxy[]                          createMany(int $number, array|callable $attributes = [])
 * @method static AccessPoint[]|Proxy[]                          createSequence(iterable|callable $sequence)
 * @method static AccessPoint[]|Proxy[]                          findBy(array $attributes)
 * @method static AccessPoint[]|Proxy[]                          randomRange(int $min, int $max, array $attributes = [])
 * @method static AccessPoint[]|Proxy[]                          randomSet(int $number, array $attributes = [])
 */
final class AccessPointFactory extends PersistentProxyObjectFactory
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
        return AccessPoint::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            // boolean() espera un porcentaje entero, no una probabilidad
            'active' => self::faker()->boolean(70),
            'name' => self::faker()->word()
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(AccessPoint $accessPoint): void {})
        ;
    }
}
