<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /** @var User */
        $user = $this->getUser();

        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $plainPassword =  $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $userRepository->save($user, true);
            $this->addFlash('success', 'Les modifications ont bien été enregistrées.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'stats' => $userRepository->findUserStats($user->getId()),
            'form' => $form->createView(),
        ]);
    }
}