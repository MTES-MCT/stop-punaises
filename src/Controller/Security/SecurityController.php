<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(
        path: '/login',
        name: 'app_login',
        defaults: ['show_sitemap' => true]
    )]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError(); // get the login error if there is one
        $lastUsername = $authenticationUtils->getLastUsername(); // last username entered by the user

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/_up/{filename}', name: 'show_uploaded_file')]
    public function showUploadedFile(string $filename)
    {
        $request = Request::createFromGlobals();
        if (!$this->isCsrfTokenValid('signalement_ext_file_view', $request->get('_csrf_token'))) {
            $this->denyAccessUnlessGranted('ENTREPRISE_VIEW');
        }

        $tmpFilepath = $this->getParameter('uploads_tmp_dir').$filename;
        $bucketFilepath = $this->getParameter('url_bucket').'/'.$filename;
        file_put_contents($tmpFilepath, file_get_contents($bucketFilepath));
        $file = new File($tmpFilepath);

        return new BinaryFileResponse($file);
    }
}
