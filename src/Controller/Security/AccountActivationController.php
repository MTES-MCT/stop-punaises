<?php

namespace App\Controller\Security;

use App\Controller\Security\Utils\ValidatorPasswordResetableTrait;
use App\Entity\Enum\Status;
use App\Entity\User;
use App\Exception\User\UserAccountAlreadyActiveException;
use App\Exception\User\UserEmailNotFoundException;
use App\Manager\UserManager;
use App\Service\Token\ActivationToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AccountActivationController extends AbstractController
{
    use ValidatorPasswordResetableTrait;

    #[Route(
        path: '/activation-compte',
        name: 'request_account_activation',
        defaults: ['show_sitemap' => true]
    )]
    public function requestPassword(
        Request $request,
        UserManager $userManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard_home');
        }

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

    #[Route(path: '/activation-compte/{uuid}/{token}', name: 'activate_account', requirements: ['token' => '.+'])]
    public function resetPassword(
        Request $request,
        ActivationToken $activationToken,
        UserManager $userManager,
        ValidatorInterface $validator,
        Security $security,
        User $user,
        string $token
    ): Response {
        if (false === $activationToken->validateToken($user, $token)) {
            $this->addFlash('error', 'Votre lien est invalide ou expiré');

            return $this->render('security/reset_password_new.html.twig', ['user' => $user, 'displayForm' => false]);
        }
        if ($security->getUser()) {
            $security->logout(false);
        }
        if ($request->isMethod('POST') &&
            $this->isCsrfTokenValid('create_password_'.$user->getId(), $request->get('_csrf_token'))
        ) {
            if ($request->get('password') !== $request->get('password-repeat')) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');

                return $this->render('security/reset_password_new.html.twig', ['user' => $user, 'displayForm' => true]);
            }
            $status = $user->getStatus();
            $user->setPassword($request->get('password'));
            $errors = $validator->validate($user, null, ['password']);
            if (\count($errors) > 0) {
                $errorMessage = '<ul>';
                foreach ($errors as $error) {
                    $errorMessage .= '<li>'.$error->getMessage().'</li>';
                }
                $errorMessage .= '</ul>';
                $this->addFlash('error error-raw', $errorMessage);

                return $this->render('security/reset_password_new.html.twig', ['user' => $user, 'displayForm' => true]);
            }
            $user = $userManager->resetPassword($user, $request->get('password'));
            if (Status::ACTIVE == $status) {
                $this->addFlash('success', 'Votre mot de passe a été mis à jour, vous pouvez vous connecter');
            } else {
                $this->addFlash('success', 'Votre compte est maintenant activé, vous pouvez vous connecter');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_new.html.twig', ['user' => $user, 'displayForm' => true]);
    }
}
