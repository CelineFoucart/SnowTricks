<?php

namespace App\Controller;

use App\Form\User\ChangePasswordFormType;
use App\Form\User\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Security\ResetPasswordHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    /**
     * @param UserRepository      $userRepository
     * @param ResetPasswordHelper $resetPasswordHelper
     */
    public function __construct(
        private UserRepository $userRepository,
        private ResetPasswordHelper $resetPasswordHelper
    ) {
    }

    /**
     * The page where the user can request to reset his password.
     *
     * @param Request $request
     */
    #[Route('/', name: 'app_request_password')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(['email' => $form->get('email')->getData()]);
            $this->resetPasswordHelper->processSendingEmail($user);

            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * The confirmation page.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('reset_password/check_email.html.twig');
    }

    /**
     * The page where the user create a new password.
     *
     * @param Request                     $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    #[Route('/reset', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->resetPasswordHelper->getUserFromResetToken($request);

        if (null === $user) {
            $this->addFlash('error', "Cette requête n'existe pas ou est expirée.");

            return $this->redirectToRoute('app_request_password');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetPassword($request);

            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($encodedPassword);
            $this->userRepository->save($user, true);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
