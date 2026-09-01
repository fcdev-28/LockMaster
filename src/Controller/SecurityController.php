<?php

namespace App\Controller;

use App\Dto\AccessCheckOutput;
use App\Entity\AccessLog;
use App\Entity\User;
use App\Form\SignUpType;
use App\Repository\AccessPointRepository;
use App\Repository\AuthorizationRuleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private AuthorizationRuleRepository $ruleRepository,
        private AccessPointRepository $accessPointRepository,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager
    ) {}

    // 'index' es el nombre que usan base.html.twig y el resto de plantillas;
    // 'landing_page' es el que usa forms/login.html.twig. Mantenemos los dos.
    #[Route('/', name: 'index')]
    #[Route('/', name: 'landing_page')]
    public function landingPage(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/login', name: 'login')]
    public function logIn(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('forms/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/signup', name: 'signup')]
    public function signUp(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = new User();
        $user->setAdministrator(false);
        $form = $this->createForm(SignUpType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user);
            $password = $form->get('password')->getData();
            $hashed = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashed);

            $userRepository->save();

            return $this->redirectToRoute('login');
        }

        return $this->render('forms/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logOut(): void
    {
        throw new \LogicException('Esto no debería ejecutarse');
    }

    /**
     * @throws Exception
     */
    // Sin #[Route]: esta acción la enruta API Platform desde el ApiResource de
    // AccessCheckOutput. Con las dos rutas sobre /api/access/check ganaba esta y
    // la respuesta no pasaba por el serializador, así que Symfony recibía el DTO
    // en crudo y devolvía un 500.
    public function checkAccess(): AccessCheckOutput
    {
        $request = $this->requestStack->getCurrentRequest();
        $accessPointId = $request->query->get('access_point_id');

        if (!$accessPointId) {
            throw new BadRequestHttpException('Missing access_point_id');
        }

        $user = $this->getUser();
        if (!$user) {
            throw new BadRequestHttpException('Not authenticated');
        }

        $accessPoint = $this->accessPointRepository->find($accessPointId);
        if (!$accessPoint) {
            throw new NotFoundHttpException('Access Point not found');
        }

        $weekday = strtolower((new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid')))->format('D'));

        // Primero intentamos por usuario directamente
        $rules = $this->ruleRepository->userHasAccess($user->getId(), $accessPointId, $weekday);

        if (count($rules) === 0) {
            foreach ($user->getUserGroups() as $group) {
                $groupRules = $this->ruleRepository->userHasAccessByGroup($group->getId(), $accessPointId, $weekday);

                if (count($groupRules) > 0) {
                    $rules = $groupRules;
                    break;
                }
            }
        }

        $hasAccess = count($rules) > 0;
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Madrid'));

        $accessLog = new AccessLog();
        $accessLog->setUser($user);
        $accessLog->setAccessPoint($accessPoint);
        $accessLog->setGranted($hasAccess);
        $accessLog->setTimestamp($now);

        if ($hasAccess) {
            $accessLog->setGrantedBy($rules[0]);
        }

        $this->entityManager->persist($accessLog);
        $this->entityManager->flush();

        return new AccessCheckOutput($hasAccess);
    }

    #[Route('/token', name: 'api_login')]
    public function apiLogin(): Response
    {
        throw $this->createAccessDeniedException('Esto no debería ejecutarse');
    }
}