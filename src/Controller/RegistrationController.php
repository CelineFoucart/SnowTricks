<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param EmailVerifier $emailVerifier
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailVerifier $emailVerifier
    ) {
    }

    /**
     * Returns the registration page.
     * 
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER'])->setCreatedAt(new DateTimeImmutable())->setIsActive(false);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->emailVerifier->sendConfirmationRequest($user, 'verify', 'app_verify_email');
            $this->addFlash('success', 'Votre compte a été créé et un email de confirmation vous a été envoyé.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Verifies an email.
     * 
     * @param Request $request
     * @param User $user
     */
    #[Route('/verify/{id}', name: 'app_verify_email')]
    public function verifyEmail(Request $request, User $user): Response
    {
        $token = $request->get('token');
        $expiredAtTimestamp = $request->get('expiredAtTimestamp');

        if (null === $token || null === $expiredAtTimestamp) {
            $this->addFlash('danger', "La validation de l'email a échoué.");
        }

        $status = $this->emailVerifier->handleEmailConfirmation($token, $expiredAtTimestamp, $user);

        if (!$status) {
            $this->addFlash('danger', "La validation de l'email a échoué.");

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre email a bien été confirmé');

        return $this->redirectToRoute('app_profile');
    }
}
