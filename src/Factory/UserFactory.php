<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Faker\Factory;
use Ottaviano\Faker\Gravatar;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<User>
 *
 * @method        User|Proxy                              create(array|callable $attributes = [])
 * @method static User|Proxy                              createOne(array $attributes = [])
 * @method static User|Proxy                              find(object|array|mixed $criteria)
 * @method static User|Proxy                              findOrCreate(array $attributes)
 * @method static User|Proxy                              first(string $sortedField = 'id')
 * @method static User|Proxy                              last(string $sortedField = 'id')
 * @method static User|Proxy                              random(array $attributes = [])
 * @method static User|Proxy                              randomOrCreate(array $attributes = [])
 * @method static UserRepository|ProxyRepositoryDecorator repository()
 * @method static User[]|Proxy[]                          all()
 * @method static User[]|Proxy[]                          createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy[]                          createSequence(iterable|callable $sequence)
 * @method static User[]|Proxy[]                          findBy(array $attributes)
 * @method static User[]|Proxy[]                          randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]|Proxy[]                          randomSet(int $number, array $attributes = [])
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    private \Faker\Generator $faker;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        // Crear la instancia de Faker
        $this->faker = Factory::create();

        // Agregar el proveedor Gravatar
        $this->faker->addProvider(new Gravatar($this->faker));
    }

    public static function class(): string
    {
        return User::class;
    }

    // Obtenemos la URL de la imagen de Gravatar y obtenemos sus datos binarios
    // Devuelve dichos datos binarios
    private function getGravatarData(string $email): ?string
    {
        /*
            Generamos la URL de Gravatar
            strtolower(): Pasamos el email a minúsculas (lo exige Gravatar)
            md5(): Calcula el hash MD5 del email (Gravatar identifica así las imágenes)
            ?s=500: Parámetro de la URL que determina el tamaño de la imagen
        */
        $avatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?s=500';

        // Iniciamos una sesión cURL para hacer una solicitud HTTP a la URL que hemos generado
        $ch = curl_init($avatarUrl);

        /*
            Configuramos las opciones de cURL
            CURLOPT_RETURNTRANSFER: Hace que el resultado de la petición se devuelva como cadena
            CURLOPT_FOLLOWLOCATION: Permite seguir redirecciones HTTP si las hay
            CURLOPT_TIMEOUT: Establece un tiempo máximo de espera de 30 segundos
        */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Indicamos que queremos la respuesta como string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Seguir cualquier redirección de URL
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);           // Establecer un tiempo máximo de espera

        // Ejecutamos la solicitud cURL
        $imageBinaryData = curl_exec($ch);

        // Verificamos si ocurrió algún error
        if(curl_errno($ch)) {
            error_log('cURL error: ' . curl_error($ch));  // Registramos el error
            curl_close($ch); // Cerramos la sesión de cURL
            return null;
        }

        // Si no hay ningún error, también cerramos la sesión
        curl_close($ch);

        // Verificamos si se obtuvieron los datos correctamente
        if ($imageBinaryData === false || empty($imageBinaryData)) {
            return null; // Si no conseguimos los datos, devolvemos null
        }

        return $imageBinaryData;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $email = self::faker()->unique()->email();
        $photoData = $this->getGravatarData($email);
        $photoStream = $photoData !== null ? fopen('php://memory', 'r+') : null;

        if ($photoStream) {
            fwrite($photoStream, $photoData);
            rewind($photoStream);
        }

        return [
            'administrator' => self::faker()->boolean(0),
            'birthDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-50 years', '-18 years')),
            'email' => $email,
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'password' => self::faker()->text(),
            // Formato español 'XXX XX XX XX' (15 caracteres), el que exige el validador de User
            'phoneNumber' => self::faker()->numerify('### ## ## ##'),
            'photo' => $photoStream,
            'username' => self::faker()->unique()->userName()
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(User $user): void {})
        ;
    }
}
