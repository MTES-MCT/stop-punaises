<?php

namespace App\Controller;

use App\Factory\MessageFactory;
use App\Manager\MessageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $message = $messageFactory->createInstanceFrom($data);

        $errors = $validator->validate($message);
        if (0 === $errors->count() && $this->isCsrfTokenValid('send_message', $data['_token'])) {
            $messageResponse = $messageManager->createMessageResponse($message);
            $response = $serializer->serialize($messageResponse, 'json');

            return $this->json($response);
        }

        return $this->json(['message' => (string) $errors], Response::HTTP_BAD_REQUEST);
    }
}
