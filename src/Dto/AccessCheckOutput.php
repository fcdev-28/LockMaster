<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Controller\SecurityController;
use Symfony\Component\Serializer\Annotation\Groups;

/*
    Hemos creado un DTO (Data Transfer Object) que es como una clase externa que coge sólo
    las propiedades necesarias de las tablas que necesitamos para saber si un usuario puede (o no)
    tener acceso a un punto de acceso.

    En este caso, cogemos la ID del usuario y la ID del punto de acceso.
*/
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/access/check',
            // Controlador que manejará la operación
            controller: SecurityController::class,
            openapiContext: [
                'parameters' => [
                    [
                        'name' => 'access_point_id',
                        'in' => 'query',
                        'required' => true,
                        'schema' => [
                            'type' => 'integer'
                        ],
                    ],
                ],
            ],
            output: AccessCheckOutput::class,
            read: false,
            name: 'access_check'
        )
    ],
    normalizationContext: ['groups' => ['access_check:read']],
    paginationEnabled: false
)]
class AccessCheckOutput
{
    #[Groups(['access_check:read'])]
    public bool $access;

    public function __construct(bool $access)
    {
        $this->access = $access;
    }
}