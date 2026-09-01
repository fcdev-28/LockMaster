<?php

namespace App\Factory;

use App\Entity\AuthorizationRule;
use App\Repository\AuthorizationRuleRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<AuthorizationRule>
 *
 * @method        AuthorizationRule|Proxy                              create(array|callable $attributes = [])
 * @method static AuthorizationRule|Proxy                              createOne(array $attributes = [])
 * @method static AuthorizationRule|Proxy                              find(object|array|mixed $criteria)
 * @method static AuthorizationRule|Proxy                              findOrCreate(array $attributes)
 * @method static AuthorizationRule|Proxy                              first(string $sortedField = 'id')
 * @method static AuthorizationRule|Proxy                              last(string $sortedField = 'id')
 * @method static AuthorizationRule|Proxy                              random(array $attributes = [])
 * @method static AuthorizationRule|Proxy                              randomOrCreate(array $attributes = [])
 * @method static AuthorizationRuleRepository|ProxyRepositoryDecorator repository()
 * @method static AuthorizationRule[]|Proxy[]                          all()
 * @method static AuthorizationRule[]|Proxy[]                          createMany(int $number, array|callable $attributes = [])
 * @method static AuthorizationRule[]|Proxy[]                          createSequence(iterable|callable $sequence)
 * @method static AuthorizationRule[]|Proxy[]                          findBy(array $attributes)
 * @method static AuthorizationRule[]|Proxy[]                          randomRange(int $min, int $max, array $attributes = [])
 * @method static AuthorizationRule[]|Proxy[]                          randomSet(int $number, array $attributes = [])
 */
final class AuthorizationRuleFactory extends PersistentProxyObjectFactory
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
        return AuthorizationRule::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        // Hacemos que si user es null, userGroup no lo sea y viceversa
        $user = self::faker()->boolean() ? UserFactory::random() : null;
        $userGroup = !$user ? UserGroupFactory::random() : null;

        // Hacemos lo mismo con los puntos de acceso
        $accessPoint = AccessPointFactory::random();

        /*
            Franjas propias de un control de accesos, en punto o media, en vez de
            horas aleatorias al segundo tipo '16:50:48 - 20:04:15'.

            La fecha da igual: tanto la vista como userHasAccess() comparan
            únicamente la hora con format('H:i:s'). Usamos una fija para que solo
            varíe la franja.
        */
        $schedules = [
            ['08:00', '20:00'],   // zonas comunes
            ['09:00', '14:00'],   // oficinas, turno de mañana
            ['16:00', '18:30'],   // oficinas, turno de tarde
            ['00:00', '23:59']    // acceso libre
        ];

        [$from, $to] = self::faker()->randomElement($schedules);

        $startTimestamp = new \DateTimeImmutable('2025-01-01 ' . $from . ':00');
        $endTimestamp = new \DateTimeImmutable('2025-01-01 ' . $to . ':00');

        return [
            'user' => $user,
            'userGroup' => $userGroup,
            'accessPoint' => $accessPoint,
            'active' => self::faker()->boolean(),
            'startTimestamp' => $startTimestamp,
            'endTimestamp' => $endTimestamp,
            'fri' => self::faker()->boolean(),
            'mon' => self::faker()->boolean(),
            'sat' => self::faker()->boolean(),
            'sun' => self::faker()->boolean(),
            'thu' => self::faker()->boolean(),
            'tue' => self::faker()->boolean(),
            'wed' => self::faker()->boolean(),
            'temp' => false
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(AuthorizationRule $authorizationRule): void {})
        ;
    }
}
