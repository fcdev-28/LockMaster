<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/account', name: 'account')]
    public function showUserData(): Response
    {
        return $this->render('pages/account/account.html.twig');
    }

    #[Route('/edit-password', name: 'edit_password')]
    public function editPassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
            );
            $userRepository->save();
            $this->addFlash('success', 'Password updated successfully');
            return $this->redirectToRoute('account');
        }

        return $this->render('pages/account/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[IsGranted('USER_EDIT', 'user', message: 'You do not have permission to edit this user')]
    #[Route('/edit-password/{id}', name: 'edit_user_password')]
    public function editUserPassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        User $user
    ): Response
    {
        $form = $this->createForm(EditPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('newPassword')->getData())
            );
            $userRepository->save();

            $this->addFlash('success', 'Contraseña cambiada correctamente');
            return $this->redirectToRoute('user_list');
        }
        return $this->render('pages/account/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN', message: 'Access denied. You do not have the necessary permissions to view this page')]
    #[Route('/users', name: 'user_list')]
    public function userList(
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $users = $userRepository->findAll();

        $pagination = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1), // página actual
            5 // elementos por página
        );

        return $this->render('pages/user/user.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/user/{id}', name: 'edit_create_user', defaults: ['id' => null])]
    public function createEditUser(
        Request $request,
        ?User $user,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // Comprobamos si estamos creando un usuario nuevo o editando uno existente
        $new = false;
        if (!$user) {
            $user = new User();
            $new = true;
        }

        /*
            Como utilizamos la misma ruta para crear y editar, no podemos gestionar las autorizaciones arriba.
            Para solucionarlo, controlamos si estamos creando o editando:
                - Creando: Utilizamos el voter de crear, que no necesita sujeto
                - Editando: Utilizamos le voter de editar y le pasamos el sujeto (usuario que estamos editando)

            denyAccessUnlessGranted() deniega el acceso a no ser que se cumpla el voter o rol que se le pase por parámetro
        */
        if ($new) {
            $this->denyAccessUnlessGranted('USER_CREATE', null, 'You do not have permission to create a user');
            $user->setPassword(
                $passwordHasher->hashPassword($user,'Temp-1234')
            );
        } else {
            $this->denyAccessUnlessGranted('USER_EDIT', $user, 'You do not have permission to edit this user');
        }

        if ($this->isGranted('ROLE_ADMIN') && $user === $this->getUser()) {
            $user->setAdministrator(true);
        } else {
            $user->setAdministrator(false);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Obtenemos del formulario el contenido del Dropzone
            $file = $form->get('uploadedPhoto')->getData();

            // Si el campo no está vacío, obtenemos los datos binarios y los asignamos al usuario
            if ($file) {
                $contenido = file_get_contents($file->getPathname());
                $user->setPhoto($contenido);
            }

            try {
                // Si es nuevo, añadimos el usuario a la base de datos
                if ($new) {
                    $userRepository->add($user);
                }

                // Guardamos los cambios
                $userRepository->save();

                // Mostramos el mensaje de éxito
                $this->addFlash('success', 'User ' . ($new ? 'created' : 'updated') . ' successfully');

                // Volvemos al listado de usuarios
                return $this->redirectToRoute('user_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Failed to save the changes');
            }
        }

        return $this->render('pages/user/edit-user.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[IsGranted('USER_DELETE', 'user', message: 'You do not have permission to delete this user')]
    #[Route('/user/delete/{id}', name: 'delete_user')]
    public function deleteUser(
        Request $request,
        UserRepository $userRepository,
        User $user
    ): Response
    {
        if ($request->request->has('confirm')) {
            try {
                $userRepository->remove($user);
                $userRepository->save();

                $this->addFlash('success', 'User deleted successfully');

                return $this->redirectToRoute('user_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Unable to delete the user');
            }
        }

        return $this->render('pages/user/confirm_delete.html.twig', [
            'user' => $user
        ]);
    }
}