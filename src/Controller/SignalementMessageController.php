<?php

namespace App\Controller;

use App\Factory\MessageFactory;
use App\Manager\MessageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignalementMessageController extends AbstractController
{
    #[Route('bo/signalements/send-messsage', name: 'app_signalement_message_send')]
    public function sendMessage(
        Request $request,
        MessageFactory $messageFactory,
        MessageManager $messageManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
    ): JsonResponse {
        $data = $request->request->all();
        $messageFactory = $messageFactory->createInstanceFrom($data);
        $messageResponse = $messageManager->createMessageResponse($messageFactory);

        $response = $serializer->serialize($messageResponse, 'json');

        return $this->json($response);
    }
}
