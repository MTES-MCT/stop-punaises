<?php

namespace App\Controller\Front;

use App\Entity\Enum\Declarant;
use App\Entity\Signalement;
use App\Form\SignalementFrontType;
use App\Manager\SignalementManager;
use App\Service\Signalement\ReferenceGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignalementController extends AbstractController
{
    #[Route('/signalement', name: 'app_front_signalement')]
    public function signalement(Request $request): Response
    {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementFrontType::class, $signalement);
        $codePostal = $request->get('code-postal');

        return $this->render('front_signalement/index.html.twig', [
            'form' => $form->createView(),
            'code_postal' => $codePostal,
        ]);
    }

    #[Route('/signalement/ajout', name: 'app_front_signalement_add')]
    public function save(Request $request, SignalementManager $signalementManager, ReferenceGenerator $referenceGenerator): Response
    {
        $signalement = new Signalement();
        $form = $this->createForm(SignalementFrontType::class, $signalement);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('front-add-signalement', $submittedToken)) {
            $signalement->setReference($referenceGenerator->generate());
            $signalement->setDeclarant(Declarant::DECLARANT_OCCUPANT);

            $data = $request->get('signalement_front');

            if (!empty($data['dejectionsTrouvees'])) {
                $dejectionsDetails = [
                    'dejectionsTrouvees' => $data['dejectionsTrouvees'],
                ];
                if ('true' == $data['dejectionsTrouvees']) {
                    $dejectionsDetails['dejectionsNombrePiecesConcernees'] = $data['dejectionsNombrePiecesConcernees'];
                    $dejectionsDetails['dejectionsFaciliteDetections'] = $data['dejectionsFaciliteDetections'];
                    $dejectionsDetails['dejectionsLieuxObservations'] = $data['dejectionsLieuxObservations'];
                }
                $signalement->setDejectionsDetails($dejectionsDetails);
            }

            if (!empty($data['oeufsEtLarvesTrouves'])) {
                $oeufsEtLarvesDetails = [
                    'oeufsEtLarvesTrouves' => $data['oeufsEtLarvesTrouves'],
                ];
                if ('true' == $data['oeufsEtLarvesTrouves']) {
                    $oeufsEtLarvesDetails['oeufsEtLarvesNombrePiecesConcernees'] = $data['oeufsEtLarvesNombrePiecesConcernees'];
                    $oeufsEtLarvesDetails['oeufsEtLarvesFaciliteDetections'] = $data['oeufsEtLarvesFaciliteDetections'];
                    $oeufsEtLarvesDetails['oeufsEtLarvesLieuxObservations'] = $data['oeufsEtLarvesLieuxObservations'];
                }
                $signalement->setOeufsEtLarvesDetails($oeufsEtLarvesDetails);
            }

            if (!empty($data['punaisesTrouvees'])) {
                $punaisesDetails = [
                    'punaisesTrouvees' => $data['punaisesTrouvees'],
                ];
                if ('true' == $data['punaisesTrouvees']) {
                    $punaisesDetails['punaisesNombrePiecesConcernees'] = $data['punaisesNombrePiecesConcernees'];
                    $punaisesDetails['punaisesFaciliteDetections'] = $data['punaisesFaciliteDetections'];
                    $punaisesDetails['punaisesLieuxObservations'] = $data['punaisesLieuxObservations'];
                }
                $signalement->setPunaisesDetails($punaisesDetails);
            }

            $signalementManager->save($signalement);

            $this->addFlash('success', 'Le signalement a bien été enregistré.');

            return $this->json(['response' => 'success']);
        }

        return $this->json(['response' => 'error', 'errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
    }
}
