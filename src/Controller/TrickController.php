<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Trick;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Service\ImageUploader;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/trick')]
class TrickController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger,
        private ImageUploader $imageUploader
    ) {
    }

    #[Route('/show/{slug}', name: 'app_trick_show', methods: ['GET', 'POST'])]
    public function show(Trick $trick, Request $request, CommentRepository $commentRepository, string $perPageComment): Response
    {
        $currentUser = $this->getUser();
        $comment = (new Comment())->setTrick($trick)->setAuthor($currentUser);
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid() && $currentUser instanceof User) { 
            $comment->setCreatedAt(new DateTimeImmutable())->setUpdatedAt(new DateTime());
            $commentRepository->save($comment, true);
            $this->addFlash( 'success', 'Votre commentaire a bien été enregistré.');

            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()], Response::HTTP_SEE_OTHER);
        }

        $comments = $commentRepository->findPaginated($trick, 0, (int) $perPageComment);
        $total = $commentRepository->countCommentByTrick($trick);

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
            'comments' => $comments,
            'perPage' => $perPageComment,
            'total' => $total,
            'currentRecords' => count($comments),
            'offset' => $perPageComment,
        ]);
    }

    #[Route('/new', name: 'app_trick_new', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER')")]
    public function new(Request $request, TrickRepository $trickRepository): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $this->setTrickAfterSubmit($trick, $form);
            $trickRepository->save($trick, true);
            $this->addFlash( 'success', 'Le trick a bien été créé.');

            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER')")]
    public function edit(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $this->setTrickAfterSubmit($trick, $form);
            $trickRepository->save($trick, true);
            $this->addFlash( 'success', 'Le trick a bien été modifié.');

            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_trick_delete', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER')")]
    public function delete(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $trickRepository->remove($trick, true);
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    private function setTrickAfterSubmit(Trick $trick, FormInterface $form): Trick
    {
        $featuredImageFile = $form->get('featuredImageFile')->getData();
        $deleteFeaturedImage = (bool) $form->get('deleteFeaturedImage')->getData();

        if ($featuredImageFile && !$deleteFeaturedImage) {
            $newFileName = $this->imageUploader->move($featuredImageFile);

            if ($trick->getFeaturedImage() !== null) {
                $this->imageUploader->remove($trick->getFeaturedImage());
            }

            $trick->setFeaturedImage($newFileName);
        }
        
        if ($deleteFeaturedImage) {
            $status = $this->imageUploader->remove($trick->getFeaturedImage());

            if ($status) {
                $trick->setFeaturedImage(null);
            }
        }

        $images = $form->get('images')->getData();
        foreach ($images as $image) {
            if ($image->getId() === null) {
                $filename = $this->imageUploader->move($image->getUploadedFile());
                $image->setFilename($filename)->setCreatedAt(new DateTimeImmutable())->setTrick($trick);
            } elseif ($image->getUploadedFile() !== null) {
                $this->imageUploader->remove($image->getFilename());
                $filename = $this->imageUploader->move($image->getUploadedFile());
                $image->setFilename($filename);
            }
        }

        $videos = $form->get('videos')->getData();
        foreach ($videos as $video) {
            $video->setTrick($trick);
        }

        $slug = $this->slugger->slug(strtolower($trick->getName()));
        $trick ->setSlug($slug)->setUpdatedAt(new DateTime());

        if (null === $trick->getAuthor()) {
            $trick->setAuthor($this->getUser());
        }

        if (null === $trick->getCreatedAt()) {
            $trick->setCreatedAt(new DateTimeImmutable());
        }

        return $trick;
    }
}
