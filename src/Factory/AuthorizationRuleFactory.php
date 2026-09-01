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
    /*
        Franjas propias de un control de accesos, en punto o media, en vez de
        horas aleatorias al segundo tipo '16:50:48 - 20:04:15'.

        La fecha da igual: tanto la vista como userHasAccess() comparan
        únicamente la hora con format('H:i:s'). Usamos una fija para que solo
        varíe la franja.
    */
    private const SCHEDULES = [
        ['08:00', '20:00'],   // zonas comunes
        ['09:00', '14:00'],   // oficinas, turno de mañana
        ['16:00', '18:30'],   // oficinas, turno de tarde
        ['00:00', '23:59']    // acceso libre
    ];

    /*
        Combinaciones de punto de acceso, sujeto y franja todavía sin usar. Cada
        regla consume una, así que dos reglas nunca comparten los tres valores y
        ningún día del panel puede repetir fila, sean cuales sean sus días.

        Van en dos bolsas, una de usuarios y otra de grupos, para conservar el
        reparto de antes: se echa una moneda al aire por regla y, si la bolsa
        que sale está vacía, se tira de la otra.
    */
    private static ?array $combinations = null;

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
        $combination = self::takeCombination();

        [$from, $to] = $combination['schedule'];

        $startTimestamp = new \DateTimeImmutable('2025-01-01 ' . $from . ':00');
        $endTimestamp = new \DateTimeImmutable('2025-01-01 ' . $to . ':00');

        return [
            'user' => $combination['user'],
            'userGroup' => $combination['userGroup'],
            'accessPoint' => $combination['accessPoint'],
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
     * Saca una combinación sin usar, preferentemente del tipo de sujeto que toque.
     *
     * @return array{user: mixed, userGroup: mixed, accessPoint: mixed, schedule: array{0: string, 1: string}}
     */
    private static function takeCombination(): array
    {
        if (self::$combinations === null) {
            self::$combinations = [
                'user' => self::buildCombinations(UserFactory::all(), 'user'),
                'userGroup' => self::buildCombinations(UserGroupFactory::all(), 'userGroup')
            ];
        }

        // Hacemos que si user es null, userGroup no lo sea y viceversa
        $order = self::faker()->boolean() ? ['user', 'userGroup'] : ['userGroup', 'user'];

        foreach ($order as $field) {
            if (self::$combinations[$field]) {
                return array_pop(self::$combinations[$field]);
            }
        }

        throw new \LogicException(
            'No quedan combinaciones únicas de punto de acceso, sujeto y franja horaria: '
            . 'crea más puntos de acceso, usuarios o grupos antes de pedir más reglas.'
        );
    }

    /**
     * Producto cartesiano de sujetos por puntos de acceso por franjas, barajado.
     *
     * @param iterable<object> $subjects
     * @param 'user'|'userGroup' $field
     */
    private static function buildCombinations(iterable $subjects, string $field): array
    {
        $accessPoints = AccessPointFactory::all();
        $combinations = [];

        foreach ($subjects as $subject) {
            foreach ($accessPoints as $accessPoint) {
                foreach (self::SCHEDULES as $schedule) {
                    $combinations[] = [
                        'user' => null,
                        'userGroup' => null,
                        $field => $subject,
                        'accessPoint' => $accessPoint,
                        'schedule' => $schedule
                    ];
                }
            }
        }

        shuffle($combinations);

        return $combinations;
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
