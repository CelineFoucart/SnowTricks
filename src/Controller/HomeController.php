<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TrickRepository $trickRepository, string $perPage): Response
    {
        $tricks = $trickRepository->findPaginated();

        return $this->render('home/index.html.twig', [
            'tricks' => $tricks,
            'total' => $trickRepository->count([]),
            'currentRecords' => count($tricks),
            'offset' => $perPage,
        ]);
    }
}
