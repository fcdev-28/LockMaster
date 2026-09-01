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

        // Hacemos que las authorizationRules puedan tener hora de inicio y final o no
        $startTimestamp = self::faker()->boolean() ? \DateTimeImmutable::createFromMutable(self::faker()->dateTime()) : null;
        $endTimestamp = null;
        if ($startTimestamp) {
            /*
                Solo cuenta la hora, no la fecha: dateTime() nos puede devolver
                '1998-03-23 23:30:00' y '2005-01-01 17:00:00', y el resto de la
                aplicación compara con format('H:i:s').

                Antes esto era un do/while que repetía dateTime() hasta sacar una
                hora posterior a la de inicio. Con una hora de inicio tardía la
                probabilidad de acertar es mínima: a las 23:59:00 hacen falta unos
                1.400 intentos de media, y a las 23:59:59 unos 86.400.
                Sorteamos directamente dentro del tramo que queda del día.
            */
            $startSeconds = ((int) $startTimestamp->format('G')) * 3600
                + ((int) $startTimestamp->format('i')) * 60
                + ((int) $startTimestamp->format('s'));

            $endSeconds = self::faker()->numberBetween($startSeconds, 86399);

            $endTimestamp = \DateTimeImmutable::createFromMutable(self::faker()->dateTime())
                ->setTime(
                    intdiv($endSeconds, 3600),
                    intdiv($endSeconds % 3600, 60),
                    $endSeconds % 60
                );
        }

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
