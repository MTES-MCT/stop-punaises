<?php

namespace App\Controller\Security;

use App\Exception\User\UserAccountAlreadyActiveException;
use App\Exception\User\UserEmailNotFoundException;
use App\Manager\UserManager;
use App\Security\AppAuthenticator;
use App\Service\Token\ActivationToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class AccountActivationController extends AbstractController
{
    #[Route(path: '/activation-compte', name: 'request_account_activation')]
    public function requestPassword(
        Request $request,
        UserManager $userManager
    ): Response {
        if ($request->isMethod('POST') && $email = $request->request->get('email')) {
            try {
                $userManager->requestActivationFrom($email);

                return $this->render('security/reset_password_link_sent.html.twig', [
                    'email' => $email,
                ]);
            } catch (UserEmailNotFoundException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('security/reset_password.html.twig');
            } catch (UserAccountAlreadyActiveException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->render('security/reset_password.html.twig');
            }
        }

        return $this->render('security/reset_password.html.twig');
    }

    #[Route(path: '/activation-compte/{token}', name: 'activate_account', requirements: ['token' => '.+'])]
    public function resetPassword(
        Request $request,
        ActivationToken $activationToken,
        UserManager $userManager,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator,
        string $token): Response
    {
        if (false === ($user = $activationToken->validateToken($token))) {
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
            'from' => 'acivate_account',
        ]);
    }
}
