<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class SitemapController extends AbstractController
{
    #[Cache(public: true, maxage: 3600)]
    #[Route('/plan-du-site', name: 'app_front_plan_du_site')]
    public function index(
        RouterInterface $router,
        #[Autowire(param: 'base_url')]
        string $baseUrl,
    ): Response {
        return $this->render('sitemap/index.html.twig');
    }

    #[Cache(public: true, maxage: 3600)]
    #[Route('/sitemap.{_format}', name: 'app_front_sitemap', defaults: ['_format' => 'xml'])]
    public function generateSitemap(
        RouterInterface $router,
        #[Autowire(param: 'base_url')]
        string $baseUrl,
    ) {
        $urls = [];
        $routes = $router->getRouteCollection()->all();
        foreach ($routes as $route) {
            if ($route->getDefaults()['show_sitemap'] ?? false) {
                $urls[] = ['loc' => $baseUrl.$route->getPath()];
            }
        }

        return new Response(
            $this->renderView('sitemap/sitemap.xml.twig', ['urls' => $urls]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/xml']
        );
    }
}
