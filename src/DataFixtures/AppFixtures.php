<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\AccessLogFactory;
use App\Factory\AccessPointFactory;
use App\Factory\AuthorizationRuleFactory;
use App\Factory\UserFactory;
use App\Factory\UserGroupFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'administrator' => true,
            'email' => 'admin@admin.com',
            'firstName' => 'Marta',
            'lastName' => 'Iglesias',
            'password' => $this->passwordHasher->hashPassword(
                new User(),
                'admin'
            ),
            'username' => 'admin'
        ]);

        UserFactory::createMany(9, [
            'password' => $this->passwordHasher->hashPassword(
                new User(),
                '1234'
            )
        ]);

        UserGroupFactory::createOne([
            'name' => 'Limpieza'
        ]);
        UserGroupFactory::createOne([
            'name' => 'Dirección'
        ]);
        UserGroupFactory::createOne([
            'name' => 'Desarrollo'
        ]);

        // Zonas de un edificio real. Dejamos un par fuera de servicio para que la
        // columna 'Active' del listado tenga variedad.
        $accessPoints = [
            'Recepción' => true,
            'Sala de servidores' => true,
            'Almacén' => true,
            'Oficinas planta 1' => true,
            'Laboratorio' => false,
            'Muelle de carga' => true,
            'Archivo' => false,
            'Sala de reuniones' => true
        ];

        foreach ($accessPoints as $name => $active) {
            AccessPointFactory::createOne([
                'name' => $name,
                'active' => $active
            ]);
        }

        AuthorizationRuleFactory::createMany(15);
        AuthorizationRuleFactory::createMany(5, [
            'temp' => true
        ]);

        AccessLogFactory::createMany(30);

        $manager->flush();
    }
}