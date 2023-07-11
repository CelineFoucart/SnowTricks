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
    #[Route('/trick/{offset}', name: 'api_trick_index')]
    public function trickIndex(int $perPageTrick, string $offset, TrickRepository $trickRepository): JsonResponse
    {
        $offset = (int) $offset;
        $html = $this->renderView('trick/_trick_list.html.twig', ['tricks' => $trickRepository->findPaginated($offset, $perPageTrick)]);
        $total = $trickRepository->count([]);

        return $this->json(
            ['offset' => $perPageTrick + $offset, 'total' => $total, 'html' => $html],
            Response::HTTP_OK,
            []
        );
    }

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
