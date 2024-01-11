<?php

namespace App\Controller;

use App\Service\Signalement\TableLister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxSignalementListController extends AbstractController
{
    #[Route('/bo/liste-signalements', name: 'app_ajax_signalement_list')]
    public function signalements(
        TableLister $tableLister,
        Request $request,
    ): JsonResponse {
        return new JsonResponse($tableLister->list($request));
    }
}
