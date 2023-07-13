<?php

namespace App\Controller\Api;

use App\Entity\Trick;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    /**
     * Returns a list of paginated tricks.
     *
     * @param int             $perPageTrick
     * @param string          $offset
     * @param TrickRepository $trickRepository
     */
    #[Route('/trick/{offset}', name: 'api_trick_index')]
    public function trickIndex(int $perPageTrick, string $offset, TrickRepository $trickRepository): JsonResponse
    {
        $offset = (int) $offset;
        $html = $this->renderView('trick/_trick_list.html.twig', ['tricks' => $trickRepository->findPaginated($offset, $perPageTrick)]);
        $total = $trickRepository->count([]);

        return $this->json(
            ['offset' => ($perPageTrick + $offset), 'total' => $total, 'html' => $html],
            Response::HTTP_OK,
            []
        );
    }

    /**
     * Returns a list of paginated comments.
     *
     * @param int               $perPageComment
     * @param string            $offset
     * @param Trick             $trick
     * @param CommentRepository $commentRepository
     */
    #[Route('/comment/{id}/{offset}', name: 'api_comment_index')]
    public function commentIndex(int $perPageComment, string $offset, Trick $trick, CommentRepository $commentRepository): JsonResponse
    {
        $offset = (int) $offset;
        $html = $this->renderView('trick/_comment_list.html.twig', [
            'comments' => $commentRepository->findPaginated($trick, $offset, $perPageComment),
        ]);
        $total = $commentRepository->countCommentByTrick($trick);

        return $this->json(
            ['offset' => $perPageComment + $offset, 'total' => $total, 'html' => $html],
            Response::HTTP_OK,
            []
        );
    }
}
