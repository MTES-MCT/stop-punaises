<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

    #[Route(
        path: '/_up/{filename}',
        name: 'show_uploaded_file',
        requirements: ['filename' => '[A-Za-z0-9_\-\.]+']
    )]
    #[IsGranted('ROLE_ENTREPRISE')]
    public function showUploadedFile(string $filename, Request $request, UriSigner $uriSigner): BinaryFileResponse
    {
        if (!$uriSigner->checkRequest($request)) {
            throw $this->createAccessDeniedException('Invalid or expired file link.');
        }

        $safeFilename = basename($filename);
        if ($safeFilename !== $filename) {
            throw $this->createNotFoundException();
        }

        $tmpDir = realpath($this->getParameter('uploads_tmp_dir'));
        $tmpFilepath = $tmpDir.'/'.$safeFilename;
        if (!str_starts_with($tmpFilepath, $tmpDir.'/')) {
            throw $this->createNotFoundException();
        }

        $bucketFilepath = rtrim($this->getParameter('url_bucket'), '/').'/'.$safeFilename;
        file_put_contents($tmpFilepath, file_get_contents($bucketFilepath));

        return new BinaryFileResponse(new File($tmpFilepath));
    }
}
