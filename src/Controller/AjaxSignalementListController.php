<?php

namespace App\Controller;

use App\Service\Signalement\TableLister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AjaxSignalementListController extends AbstractController
{
    #[Route('/bo/liste-signalements', name: 'app_ajax_signalement_list')]
    public function signalements(
        SerializerInterface $serializer,
        TableLister $tableLister,
        Request $request,
    ): Response {
        $signalementsSerialized = $serializer->serialize($tableLister->list($request), 'json');

        return new Response(
            $signalementsSerialized,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
