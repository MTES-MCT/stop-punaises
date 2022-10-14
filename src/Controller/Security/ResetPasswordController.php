<?php

namespace App\Controller\Security;

use App\Exception\User\RequestPasswordNotAllowedException;
use App\Exception\User\UserEmailNotFoundException;
use App\Manager\UserManager;
use App\Security\AppAuthenticator;
use App\Service\Token\ResetPasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route(path: '/mot-de-passe-perdu', name: 'request_password')]
    public function requestPassword(
        Request $request,
        UserManager $userManager
    ): Response {
        if ($request->isMethod('POST') && $email = $request->request->get('email')) {
            try {
                $userManager->requestPasswordFrom($email);
            } catch (UserEmailNotFoundException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('security/reset_password.html.twig');
            } catch (RequestPasswordNotAllowedException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('security/reset_password.html.twig');
            }

            return $this->render('security/reset_password_link_sent.html.twig', [
                'email' => $email,
            ]);
        }

        return $this->render('security/reset_password.html.twig');
    }

    #[Route(path: '/nouveau-mot-de-passe/{token}', name: 'reset_password', requirements: ['token' => '.+'])]
    public function resetPassword(
        Request $request,
        ResetPasswordToken $resetPasswordToken,
        UserManager $userManager,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator,
        string $token): Response
    {
        if (false === ($user = $resetPasswordToken->validateToken($token))) {
            $this->addFlash('error', 'Votre lien a expiré');

            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) { /* @todo: check csrf_token */
            $user = $userManager->resetPassword($user, $request->get('password'));

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('security/reset_password_new.html.twig', [
            'email' => $user->getEmail(),
            'id' => $user->getId(),
            'from' => 'reset_password',
        ]);
    }
}
