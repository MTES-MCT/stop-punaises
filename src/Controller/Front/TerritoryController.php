<?php

namespace App\Controller\Front;

use App\Repository\TerritoireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TerritoryController extends AbstractController
{
    #[Route('/territoires/active', name: 'app_territory_active')]
    public function index(TerritoireRepository $territoireRepository): JsonResponse
    {
        return $this->json($territoireRepository->findActiveTerritoires('t.zip'));
    }
}
