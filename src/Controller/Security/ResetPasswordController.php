<?php

namespace App\Controller\Security;

use App\Controller\Security\Utils\ValidatorPasswordResetableTrait;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    use ValidatorPasswordResetableTrait;

    #[Route(path: '/mot-de-passe-perdu',
        name: 'request_password',
        defaults: ['show_sitemap' => true]
    )]
    public function requestPassword(
        Request $request,
        UserManager $userManager,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard_home');
        }

        if ($request->isMethod('POST') && $email = $request->request->get('email')) {
            $userManager->requestPasswordFrom($email);

            return $this->render('security/reset_password_link_sent.html.twig', [
                'email' => $email,
            ]);
        }

        return $this->render('security/reset_password.html.twig');
    }
}
