<?php

/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Security\Voter\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserServiceInterface;
use App\Repository\UrlRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\Type\ChangePasswordType;
use App\Repository\UserRepository;

/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface $userService User service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route('/user', name: 'user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->userService->getPaginatedUsers($page);

        return $this->render('user/index.html.twig', [
            'users' => $pagination,
        ]);
    }

    /**
     * View action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route('/user/{id}', name: 'user_view', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(UserVoter::VIEW, subject: 'user')]
    #[IsGranted('ROLE_ADMIN')]
    public function view(User $user): Response
    {
        return $this->render('user/view.html.twig', ['user' => $user]);
    }

    /**
     * My profile action.
     *
     * @param UrlRepository $urlRepository URL repository
     *
     * @return Response HTTP response
     */
    #[Route('/user/me', name: 'user_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(UrlRepository $urlRepository): Response
    {
        $user = $this->getUser();
        $urls = $urlRepository->findBy(['author' => $user]);

        return $this->render('user/me.html.twig', [
            'user' => $user,
            'urls' => $urls,
        ]);
    }

    /**
     * Edit user action.
     *
     * @param Request                $request        HTTP request
     * @param User                   $user           User entity
     * @param EntityManagerInterface $em             Entity manager
     * @param UserRepository         $userRepository User repository
     *
     * @return Response HTTP response
     */
    #[Route('/user/{id}/edit', name: 'user_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::EDIT, subject: 'user')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $originalRoles = $user->getRoles();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentRoles = $user->getRoles();

            $wasAdmin = in_array('ROLE_ADMIN', $originalRoles, true);
            $isStillAdmin = in_array('ROLE_ADMIN', $currentRoles, true);

            $adminCount = $userRepository->countAdmins();

            if ($wasAdmin && !$isStillAdmin && $adminCount <= 1) {
                $this->addFlash('danger', $this->translator->trans('message.lastAdmin'));
                $em->refresh($user);

                return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
            }

            $em->flush();
            $this->addFlash('success', $this->translator->trans('message.editedSuccessfully'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Edit my profile action.
     *
     * @param Request                $request HTTP request
     * @param EntityManagerInterface $em      Entity manager
     *
     * @return Response HTTP response
     */
    #[Route('/user/me/edit', name: 'user_edit_me', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editMe(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', $this->translator->trans('message.profileUpdated'));

            return $this->redirectToRoute('user_me');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Block user action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/user/{id}/block', name: 'user_block', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::BLOCK, subject: 'user')]
    #[IsGranted('ROLE_ADMIN')]
    public function block(Request $request, User $user): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('user_block', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $this->addFlash('danger', $this->translator->trans('message.cannotBlockAdmin'));

                return $this->redirectToRoute('user_index');
            }

            $this->userService->blockUser($user);

            $this->addFlash('success', $this->translator->trans('message.userBlocked'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/block.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Unblock user action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/{id}/unblock', name: 'user_unblock', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::BLOCK, subject: 'user')]
    #[IsGranted('ROLE_ADMIN')]
    public function unblock(Request $request, User $user): Response
    {
        $form = $this->createForm(FormType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('user_unblock', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->unblockUser($user);

            $this->addFlash('success', $this->translator->trans('message.userUnblocked'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/unblock.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Change password action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route('/user/{id}/change-password', name: 'user_change_password', methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::EDIT, subject: 'user')]
    #[IsGranted('ROLE_ADMIN')]
    public function changePassword(Request $request, User $user): Response
    {
        $form = $this->createForm(ChangePasswordType::class, $user, [
            'is_admin' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $this->userService->changePassword($user, $plainPassword);

            $this->addFlash('success', $this->translator->trans('message.passwordChanged'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
