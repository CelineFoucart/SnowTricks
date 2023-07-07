<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\ResetPassword;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Security\ResetPasswordHelper;
use App\Form\User\ChangePasswordFormType;
use App\Repository\ResetPasswordRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Form\User\ResetPasswordRequestFormType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private ResetPasswordHelper $resetPasswordHelper
    ) {
    }

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

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('reset_password/check_email.html.twig');
    }

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
