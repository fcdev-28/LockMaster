<?php

namespace App\Controller;

use App\Entity\AccessLog;
use App\Repository\AccessLogRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessLogController extends AbstractController
{
    #[Route('/access-log', name: 'access_log')]
    public function listAccessLog(
        AccessLogRepository $accessLogRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $user = $this->getUser();

        // Usamos un QueryBuilder porque Paginator necesita un QueyBuilder, no una lista ya cargada
        if ($this->isGranted('ROLE_ADMIN', $user)) {
            $queryBuilder = $accessLogRepository->findAllOrderByTimestamp();
        } else {
            $queryBuilder = $accessLogRepository->findByUser($user);
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // página actual
            8 // elementos por página
        );

        return $this->render('pages/access_log/access_log.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/access-log/delete/{id}', name: 'access_log_delete')]
    public function deleteAccessLog(
        Request $request,
        AccessLogRepository $accessLogRepository,
        AccessLog $accessLog
    ): Response
    {
        if ($request->request->has('confirm')) {
            try {
                $accessLogRepository->remove($accessLog);
                $accessLogRepository->save();

                $this->addFlash('success', 'Access log deleted successfully');

                return $this->redirectToRoute('access_log');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to save the changes');
            }
        }

        return $this->render('pages/access_log/confirm_delete.html.twig', [
            'log' => $accessLog
        ]);
    }

    #[Route('/access-log/delete-history', name: 'delete_history')]
    public function deleteHistory(
        Request $request,
        AccessLogRepository $accessLogRepository,
    ): Response
    {
        $user = $this->getUser();

        if ($request->request->has('confirm')) {
            try {
                if ($this->isGranted('ROLE_ADMIN', $user)) {
                    $accessLogRepository->clear();
                } else {
                    $accessLogRepository->clearByUser($user);
                }

                $accessLogRepository->save();

                $this->addFlash('success', 'History deleted successfully');

                return $this->redirectToRoute('access_log');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to save the changes');
            }
        }

        return $this->render('pages/access_log/delete_history.html.twig');
    }
}