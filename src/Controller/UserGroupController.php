<?php

namespace App\Controller;

use App\Entity\UserGroup;
use App\Form\UserGroupType;
use App\Repository\UserGroupRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserGroupController extends AbstractController
{
    #[IsGranted('GROUP_LIST', message: 'You do not have permission to list the user groups')]
    #[Route('/user-groups', name: 'user_group_list')]
    public function listUserGroups(
        UserGroupRepository $userGroupRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response
    {
        $groups = $userGroupRepository->findAll();

        $pagination = $paginator->paginate(
            $groups,
            $request->query->getInt('page', 1), // página actual
            5 // elementos por página
        );

        return $this->render('pages/user_group/user_group.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/user-groups/edit/{id}', name: 'edit_create_user_group', defaults: ['id' => null])]
    public function createEditUserGroup(
        Request $request,
        UserGroupRepository $userGroupRepository,
        ?UserGroup $userGroup
    ): Response
    {
        $new = false;
        if (!$userGroup) {
            $userGroup = new UserGroup();
            $new = true;
        }

        if ($new) {
            $this->denyAccessUnlessGranted('GROUP_CREATE', null, 'You do not have permission to create a group');
        } else {
            $this->denyAccessUnlessGranted('GROUP_EDIT', $userGroup, 'You do not have permission to edit this group');
        }

        $form = $this->createForm(UserGroupType::class, $userGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userGroupRepository->add($userGroup);
                $userGroupRepository->save();

                $this->addFlash('success', 'Group ' . ($new ? 'created' : 'updated') . ' successfully');

                return $this->redirectToRoute('user_group_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to save the changes');
            }
        }

        return $this->render('pages/user_group/edit_create_user_group.html.twig', [
            'form' => $form->createView(),
            'group' => $userGroup
        ]);
    }

    #[IsGranted('GROUP_DELETE', 'userGroup', message: 'You do not have permission to delete this group')]
    #[Route('/user-groups/delete/{id}', name: 'delete_user_group')]
    public function deleteUser(
        Request $request,
        UserGroupRepository $userGroupRepository,
        UserGroup $userGroup
    ): Response
    {
        if ($request->request->has('confirm')) {
            try {
                $userGroupRepository->remove($userGroup);
                $userGroupRepository->save();

                $this->addFlash('success', 'Group deleted successfully');

                return $this->redirectToRoute('user_group_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Unable to delete the group');
            }
        }

        return $this->render('pages/user_group/confirm_delete.html.twig', [
            'user' => $userGroup
        ]);
    }
}