<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private ImageUploader $imageUploader
    ) {
    }

    /**
     * Returns the profile page where the user can update his informations.
     *
     * @param UserRepository              $userRepository
     * @param Request                     $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    #[Route('/profile', name: 'app_profile')]
    public function profile(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $avatar = $form->get('uploadedFile')->getData();
            if ($avatar) {
                $newFileName = $this->imageUploader->move($avatar, 'avatar');
            }

            if (null !== $user->getAvatar()) {
                $this->imageUploader->remove($user->getAvatar(), 'avatar');
            }

            $user->setAvatar($newFileName);

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
