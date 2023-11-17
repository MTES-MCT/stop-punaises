<?php

namespace App\Controller\Front;

use App\Repository\TerritoireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TerritoryController extends AbstractController
{
    #[Route('/territoire/active', name: 'ws_territory_active')]
    public function index(TerritoireRepository $territoireRepository): JsonResponse
    {
        return $this->json($territoireRepository->findActiveTerritoire('t.zip'));
    }
}
