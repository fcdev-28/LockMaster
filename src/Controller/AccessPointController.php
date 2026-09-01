<?php

namespace App\Controller;

use App\Entity\AccessPoint;
use App\Entity\AuthorizationRule;
use App\Form\AccessPointType;
use App\Form\RestrictionType;
use App\Repository\AccessPointRepository;
use App\Repository\AuthorizationRuleRepository;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessPointController extends AbstractController
{
    #[IsGranted('ACCESS_POINT_LIST', message: 'Yo do not have permission to list access points')]
    #[Route('/access-point', name: 'ap_list')]
    public function apList(
        AccessPointRepository $accessPointRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $aps = $accessPointRepository->findAll();

        $pagination = $paginator->paginate(
            $aps,
            $request->query->getInt('page', 1), // página actual
            5 // elementos por página
        );

        return $this->render('pages/access_point/access_point.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/ap/edit/{id}', name: 'ap_edit', defaults: ['id' => null])]
    public function editAccessPoint(
        Request $request,
        AccessPointRepository $accessPointRepository,
        ?AccessPoint $accessPoint
    ): Response
    {
        // Comprobamos si estamos creando un punto de acceso nuevo o editando uno existente
        $new = false;
        if (!$accessPoint) {
            $accessPoint = new AccessPoint();
            $new = true;
        }

        if ($new) {
            $this->denyAccessUnlessGranted(
                'ACCESS_POINT_CREATE',
                null,
                'You do not have permission to create a access point'
            );
        } else {
            $this->denyAccessUnlessGranted(
                'ACCESS_POINT_EDIT',
                $accessPoint,
                'You do not have permission to edit this access point'
            );
        }

        $form = $this->createForm(AccessPointType::class, $accessPoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Si es nuevo, añadimos el punto de acceso a la base de datos
                if ($new) {
                    $accessPointRepository->add($accessPoint);
                }

                // Guardamos los cambios
                $accessPointRepository->save();

                // Mostramos el mensaje de éxito
                $this->addFlash('success', 'Access point ' . ($new ? 'created' : 'updated') . ' successfully');

                // Volvemos al listado de usuarios
                return $this->redirectToRoute('ap_list');
            } catch (Exception $e) {
                $this->addFlash('error', 'Failed to save the changes');
            }
        }

        return $this->render('pages/access_point/edit_access_point.html.twig', [
            'form' => $form->createView(),
            'ap' => $accessPoint
        ]);
    }

    /**
     * @throws Exception
     */
    #[IsGranted('AUTH_CREATE', message: 'You do not have permission to create a restriction')]
    #[Route('/ap/auth/{id}', name: 'ap_auth')]
    public function editRestrictions(
        Request $request,
        AuthorizationRuleRepository $authorizationRuleRepository,
        AccessPoint $accessPoint
    ): Response
    {
        // Obtenemos todas las restricciones del punto de acceso deseado
        $authorizationRuleRepository->checkAndMarkExpired();
        $ars = $authorizationRuleRepository->findAllByAccessPoint($accessPoint);
        $groupedRules = [];

        $rule = new AuthorizationRule();
        $rule->setAccessPoint($accessPoint);

        $form = $this->createForm(RestrictionType::class, $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $authorizationRuleRepository->checkAndMarkExpired();
                $authorizationRuleRepository->add($rule);
                $authorizationRuleRepository->save();

                $this->addFlash('success', 'Restriction added successfully');

                return $this->redirectToRoute('ap_list');
            } catch (Exception $e) {
                $this->addFlash('error', 'Failed to save the changes');
            }
        }

        // Recorremos todas las restricciones
        foreach ($ars as $rule) {
            // Recorremos todos los días de la semana
            // Hacemos un mapeo entre el nombre de la propiedad y nombre del día
            foreach ([
                         'mon' => 'Mon',
                         'tue' => 'Tue',
                         'wed' => 'Wed',
                         'thu' => 'Thu',
                         'fri' => 'Fri',
                         'sat' => 'Sat',
                         'sun' => 'Sun',
                     ] as $key => $dayName) {
                // $rule->{'is' . ucfirst($key)}() sirve para coger todos los métodos isDiaSemana. Por ejemplo: 'isMon'
                if ($rule->{'is' . ucfirst($key)}()) {
                    // Cogemos las horas de esa restricción
                    $start = $rule->getStartTimestamp();
                    $end = $rule->getEndTimestamp();

                    // Si no es null, convertimos a hora legible
                    $startStr = $start ? $start->format('H:i') : null;
                    $endStr = $end ? $end->format('H:i') : null;

                    // Si no existía aún ese día
                    if (!isset($groupedRules[$dayName])) {
                        $groupedRules[$dayName] = [
                            'id' => $rule
                        ];
                    } else {
                        // Comparamos y ajustamos horarios para ver si son más amplios que los ya guardados
                        if ($startStr !== null && ($groupedRules[$dayName]['start'] === null || $startStr < $groupedRules[$dayName]['start'])) {
                            $groupedRules[$dayName]['start'] = $startStr;
                        }
                        if ($endStr !== null && ($groupedRules[$dayName]['end'] === null || $endStr > $groupedRules[$dayName]['end'])) {
                            $groupedRules[$dayName]['end'] = $endStr;
                        }
                    }
                }
            }
        }

        // Ordenamos el array groupedRules
        // Creamos un array con el orden deseado
        $dayOrder = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        // Utilizamos uksort() para ordenar en un orden personalizado que no sea alfabético
        uksort($groupedRules, function ($a, $b) use ($dayOrder) {
            return array_search($a, $dayOrder) <=> array_search($b, $dayOrder);
        });

        return $this->render('pages/authorization_rule/edit_restrictrions.html.twig', [
            'form' => $form->createView(),
            'ap' => $accessPoint,
            'groupedRules' => $groupedRules
        ]);
    }

    #[IsGranted('ACCESS_POINT_DELETE', 'accessPoint', 'You do not have permission to delete this access point')]
    #[Route('/ap/delete/{id}', name: 'ap_delete')]
    public function deleteAccessPoint(
        Request $request,
        AccessPointRepository $accessPointRepository,
        AccessPoint $accessPoint
    ): Response
    {
        if ($request->request->has('confirm')) {
            try {
                $accessPointRepository->remove($accessPoint);
                $accessPointRepository->save();

                $this->addFlash('success', 'Access point deleted successfully');

                return $this->redirectToRoute('ap_list');
            } catch (Exception $e) {
                $this->addFlash('error', 'Unable to delete the access point');
            }
        }

        return $this->render('pages/access_point/confirm_delete.html.twig', [
            'ap' => $accessPoint
        ]);
    }
}