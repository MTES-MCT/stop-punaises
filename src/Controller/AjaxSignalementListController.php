<?php

namespace App\Controller;

use App\Dto\DataTableRequest;
use App\Service\Signalement\SignalementOccupantDataTableHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AjaxSignalementListController extends AbstractController
{
    #[Route('/bo/liste-signalements', name: 'app_ajax_signalement_list')]
    public function signalements(
        SignalementOccupantDataTableHandler $signalementOccupantDataTableHandler,
        DataTableRequest $dataTableRequest,
    ): JsonResponse {
        $dataTableResponse = $signalementOccupantDataTableHandler->handleRequest($dataTableRequest);

        return new JsonResponse($dataTableResponse->toArray());
    }
}
