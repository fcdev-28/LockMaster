<?php

namespace App\Controller;

use App\Entity\AccessPoint;
use App\Entity\AuthorizationRule;
use App\Form\TemporaryAccessType;
use App\Repository\AuthorizationRuleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorizationRuleController extends AbstractController
{
    #[IsGranted('AUTH_CREATE', message: 'You do not have permission to grant a temporary access')]
    #[Route('/auth/temp/{id}', name: 'temp_auth')]
    public function createTempAccess(
        Request $request,
        AuthorizationRuleRepository $authorizationRuleRepository,
        AccessPoint $accessPoint
    ): Response
    {
        $ar = new AuthorizationRule();
        $ar->setAccessPoint($accessPoint);

        $form = $this->createForm(TemporaryAccessType::class, $ar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $seconds = $form->get('seconds')->getData();
                $minutes = $form->get('minutes')->getData();
                $hours   = $form->get('hours')->getData();
                $days    = $form->get('days')->getData();

                $total = $seconds +
                    ($minutes * 60) +
                    ($hours * 3600) +
                    ($days * 86400);

                $timestamp = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid'));

                $endTime = new \DateTimeImmutable("+{$total} seconds", new \DateTimeZone("Europe/Madrid"));

                $ar->setStartTimestamp($timestamp);
                $ar->setEndTimestamp($endTime);

                $authorizationRuleRepository->checkAndMarkExpired();
                $authorizationRuleRepository->add($ar);
                $authorizationRuleRepository->save();

                $this->addFlash('success', 'Temporary access granted successfully');

                return $this->redirectToRoute('ap_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to save the changes');
            }
        }

        return $this->render('pages/authorization_rule/temporary_access.html.twig', [
            'form' => $form->createView(),
            'ap' => $accessPoint
        ]);
    }

    #[IsGranted('AUTH_DELETE', 'authorizationRule', message: 'You do not have permission to delete a restriction')]
    #[Route('/auth/delete/{id}/{weekday}', name: 'auth_delete')]
    public function deleteRestriction(
        Request $request,
        AuthorizationRuleRepository $authorizationRuleRepository,
        AuthorizationRule $authorizationRule,
        string $weekday
    ): Response
    {
        if ($request->request->has('confirm')) {
            try {
                $setter = 'set' . ucfirst($weekday);
                $authorizationRule->$setter(false);

                $authorizationRuleRepository->checkAndMarkExpired();
                $authorizationRuleRepository->save();

                $this->addFlash('success', 'Restriction deleted successfully');

                return $this->redirectToRoute('ap_auth', [
                    'id' => $authorizationRule->getAccessPoint()->getId()
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Unable to delete the restriction');
            }
        }

        return $this->render('pages/authorization_rule/confirm_delete.html.twig', [
            'ar' => $authorizationRule
        ]);
    }
}
