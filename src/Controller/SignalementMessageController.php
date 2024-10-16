<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\MessageThread;
use App\Entity\Signalement;
use App\Factory\MessageFactory;
use App\Manager\MessageManager;
use App\Manager\MessageThreadManager;
use App\Repository\InterventionRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
    public function sendMessageToUsager(
        Request $request,
        #[MapEntity(mapping: ['signalement_uuid' => 'uuid'])]
        Signalement $signalement,
        #[MapEntity(mapping: ['entreprise_uuid' => 'uuid'])]
        Entreprise $entreprise,
        MessageThreadManager $messageThreadManager,
        MessageFactory $messageFactory,
        MessageManager $messageManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        InterventionRepository $interventionRepository,
    ): JsonResponse {
        $entrepriseIntervention = $interventionRepository->findBySignalementAndEntreprise(
            $signalement,
            $entreprise
        );
        if (!$entrepriseIntervention || !$entrepriseIntervention->isAccepted()) {
            return $this->json(['status' => 'denied'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->request->all();
        $messageThread = $messageThreadManager->createOrGet($signalement, $entreprise);
        $message = $messageFactory->createInstanceFrom(
            messageThread: $messageThread,
            sender: $entreprise->getUser()->getEmail(),
            recipient: $signalement->getEmailOccupant(),
            message : $data['message']
        );

        /** @var ConstraintViolationList $errors */
        $errors = $validator->validate($message);
        if (0 === $errors->count() && $this->isCsrfTokenValid('send_message', $data['_token'])) {
            $messageResponse = $messageManager->createMessageResponse($message, $this->getUser());
            $response = $serializer->serialize($messageResponse, 'json');

            return $this->json($response);
        }

        return $this->json(['message' => (string) $errors], Response::HTTP_BAD_REQUEST);
    }

    #[Route('messages-thread/{uuid}/send-messsage', name: 'app_public_thread_message_send')]
    public function sendMessageToEntreprise(
        Request $request,
        MessageThread $messageThread,
        MessageFactory $messageFactory,
        MessageManager $messageManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
    ): JsonResponse {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->json(['message' => 'Vous ne pouvez pas envoyer de message en tant qu\'admin.'], Response::HTTP_FORBIDDEN);
        }
        $entreprise = $messageThread->getEntreprise();
        if (!$entreprise || !$entreprise->isActive()) {
            return $this->json(['message' => 'L\'entreprise n\'existe pas ou n\'est pas active.'], Response::HTTP_BAD_REQUEST);
        }
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
