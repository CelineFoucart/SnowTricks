<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Service\ImageUploader;
use App\Service\TrickMediaFactory;
use DateTime;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/trick')]
class TrickController extends AbstractController
{
    /**
     * @param SluggerInterface $slugger
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        private SluggerInterface $slugger,
        private ImageUploader $imageUploader
    ) {
    }

    /**
     * Returns the show page.
     * 
     * @param Trick $trick
     * @param Request $request
     * @param CommentRepository $commentRepository
     * @param string $perPageComment
     * 
     * @return Response
     */
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
            $this->addFlash('success', 'Votre commentaire a bien été enregistré.');

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

    /**
     * Returns the creation trick page.
     * 
     * @param Request $request
     * @param TrickRepository $trickRepository
     * 
     * @return Response
     */
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
            $this->addFlash('success', 'Le trick a bien été créé.');

            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    /**
     * Returns the editing trick page.
     * 
     * @param Request $request
     * @param Trick $trick
     * @param TrickRepository $trickRepository
     * 
     * @return Response
     */
    #[Route('/edit/{id}', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    #[Security("is_granted('ROLE_USER')")]
    public function edit(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $this->setTrickAfterSubmit($trick, $form);
            $trickRepository->save($trick, true);
            $this->addFlash('success', 'Le trick a bien été modifié.');

            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    /**
     * Deletes a trick if the token CSRF is valid.
     * 
     * @param Request $request
     * @param Trick $trick
     * @param TrickRepository $trickRepository
     * 
     * @return Response
     */
    #[Route('/{id}', name: 'app_trick_delete', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER')")]
    public function delete(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trick->getId(), $request->request->get('_token'))) {
            $trickRepository->remove($trick, true);
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Set the trick medias.
     * 
     * @param Trick $trick
     * @param FormInterface $form
     * 
     * @return Trick
     */
    private function setTrickAfterSubmit(Trick $trick, FormInterface $form): Trick
    {
        $trick = (new TrickMediaFactory($this->imageUploader))
            ->setTrick($trick)
            ->setFeaturedImageFile($form)
            ->setGallery($form)
            ->setVideoGallery($form)
            ->getTrick()
        ;

        $slug = $this->slugger->slug(strtolower($trick->getName()));
        $trick->setSlug($slug)->setUpdatedAt(new DateTime());

        if (null === $trick->getAuthor()) {
            $trick->setAuthor($this->getUser());
        }

        if (null === $trick->getCreatedAt()) {
            $trick->setCreatedAt(new DateTimeImmutable());
        }

        return $trick;
    }
}
