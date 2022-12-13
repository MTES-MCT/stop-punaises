<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Factory\MessageFactory;
use App\Manager\MessageManager;
use App\Manager\MessageThreadManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignalementMessageController extends AbstractController
{
    #[Route('bo/signalements/{signalement_uuid}/entreprises/{entreprise_uuid}/send-messsage',
        name: 'app_private_thread_message_send')]
    #[ParamConverter('signalement', options: ['mapping' => ['signalement_uuid' => 'uuid']])]
    #[ParamConverter('entreprise', options: ['mapping' => ['entreprise_uuid' => 'uuid']])]
    public function sendMessageToUsager(
        Request $request,
        Signalement $signalement,
        Entreprise $entreprise,
        MessageThreadManager $messageThreadManager,
        MessageFactory $messageFactory,
        MessageManager $messageManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
    ): JsonResponse {
        $data = $request->request->all();
        $messageThread = $messageThreadManager->createOrGet($signalement, $entreprise);
        $message = $messageFactory->createInstanceFrom(
            messageThread: $messageThread,
            sender: $entreprise->getUser()->getEmail(),
            recipient: $signalement->getEmailOccupant(),
            message : $data['message']
        );

        $errors = $validator->validate($message);
        if (0 === $errors->count() && $this->isCsrfTokenValid('send_message', $data['_token'])) {
            $messageResponse = $messageManager->createMessageResponse($message, $this->getUser());
            $response = $serializer->serialize($messageResponse, 'json');

            return $this->json($response);
        }

        return $this->json(['message' => (string) $errors], Response::HTTP_BAD_REQUEST);
    }

    #[Route('messages-thread/{uuid}/send-messsage',
        name: 'app_public_thread_message_send')]
    public function sendMessageToEntreprise(
        Request $request,
        MessageThread $messageThread,
        MessageThreadManager $messageThreadManager,
        MessageFactory $messageFactory,
        MessageManager $messageManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
    ): JsonResponse {
        $data = $request->request->all();
        $message = $messageFactory->createInstanceFrom(
            messageThread: $messageThread,
            sender: $messageThread->getSignalement()->getEmailOccupant(),
            recipient: $messageThread->getEntreprise()->getUser()->getEmail(),
            message : $data['message']
        );

        $errors = $validator->validate($message);
        if (0 === $errors->count() && $this->isCsrfTokenValid('send_message', $data['_token'])) {
            $messageResponse = $messageManager->createMessageResponse($message);
            $response = $serializer->serialize($messageResponse, 'json');

            return $this->json($response);
        }

        return $this->json(['message' => $this->getErrors($errors)], Response::HTTP_BAD_REQUEST);
    }

    private function getErrors(ConstraintViolationList $constraintViolationList): string
    {
        $errors = [];
        /** @var ConstraintViolation $constraint */
        foreach ($constraintViolationList as $constraint) {
            $errors[] = $constraint->getMessage();
        }

        return implode(',', $errors);
    }
}
