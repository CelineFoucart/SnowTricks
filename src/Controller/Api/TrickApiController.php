<?php

namespace App\Controller\Api;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class TrickApiController extends AbstractController
{
    #[Route('/trick/{offset}', name: 'api_trick_index')]
    public function trickIndex(int $perPage, string $offset, TrickRepository $trickRepository): JsonResponse
    {
        $offset = (int) $offset;
        $html = $this->renderView("trick/_trick_list.html.twig", ['tricks' => $trickRepository->findPaginated($offset, $perPage)]);
        $total = $trickRepository->count([]);
        
        return $this->json(
            ['offset' => $perPage + $offset, 'total' => $total, 'tricks' => $html], 
            Response::HTTP_OK,
            []
        );
    }
}