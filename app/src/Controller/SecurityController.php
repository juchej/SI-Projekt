<?php

/**
 * Security controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Type\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\Type\RegisterType;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SecurityController.
 */
class SecurityController extends AbstractController
{
    /**
     * Login action.
     *
     * @param AuthenticationUtils $authenticationUtils Authentication utilities
     * @param TranslatorInterface $translator          Translator
     *
     * @return Response HTTP response
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        if ($this->getUser() instanceof UserInterface) {
            $this->addFlash('success', $translator->trans('message.loggedInSuccessfully'));

            return $this->redirectToRoute('url_index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Register action.
     *
     * @param Request                     $request        HTTP request
     * @param EntityManagerInterface      $entityManager  Entity manager
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param TranslatorInterface         $translator     Translator
     *
     * @return Response HTTP response
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('message.registrationSuccessful'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Logout action.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Change password action.
     *
     * @param Request                     $request        HTTP request
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     * @param EntityManagerInterface      $entityManager  Entity manager
     * @param TranslatorInterface         $translator     Translator
     *
     * @return Response HTTP response
     */
    #[Route('/change-password', name: 'change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class, $user, [
            'is_admin' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            $entityManager->flush();

            $this->addFlash('success', $translator->trans('message.passwordChanged'));

            return $this->redirectToRoute('url_index');
        }

        return $this->render('security/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
