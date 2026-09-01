<?php

namespace App\Factory;

use App\Entity\UserGroup;
use App\Repository\UserGroupRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<UserGroup>
 *
 * @method        UserGroup|Proxy                              create(array|callable $attributes = [])
 * @method static UserGroup|Proxy                              createOne(array $attributes = [])
 * @method static UserGroup|Proxy                              find(object|array|mixed $criteria)
 * @method static UserGroup|Proxy                              findOrCreate(array $attributes)
 * @method static UserGroup|Proxy                              first(string $sortedField = 'id')
 * @method static UserGroup|Proxy                              last(string $sortedField = 'id')
 * @method static UserGroup|Proxy                              random(array $attributes = [])
 * @method static UserGroup|Proxy                              randomOrCreate(array $attributes = [])
 * @method static UserGroupRepository|ProxyRepositoryDecorator repository()
 * @method static UserGroup[]|Proxy[]                          all()
 * @method static UserGroup[]|Proxy[]                          createMany(int $number, array|callable $attributes = [])
 * @method static UserGroup[]|Proxy[]                          createSequence(iterable|callable $sequence)
 * @method static UserGroup[]|Proxy[]                          findBy(array $attributes)
 * @method static UserGroup[]|Proxy[]                          randomRange(int $min, int $max, array $attributes = [])
 * @method static UserGroup[]|Proxy[]                          randomSet(int $number, array $attributes = [])
 */
final class UserGroupFactory extends PersistentProxyObjectFactory
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
        return UserGroup::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(255),
            'users' => UserFactory::randomRange(1, 4)
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(UserGroup $userGroup): void {})
        ;
    }
}
