<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class SitemapController extends AbstractController
{
    #[Route('/plan-du-site', name: 'app_front_plan_du_site')]
    public function index(
        RouterInterface $router,
        #[Autowire(param: 'base_url')]
        string $baseUrl,
    ): Response {
        $titles = [];
        $routes = $router->getRouteCollection()->all();
        foreach ($routes as $route) {
            if (isset($route->getDefaults()['sitemap_title_page'])) {
                $titles[$route->getDefault('sitemap_category') ?? 'others'][] = [
                    'name' => $route->getDefault('sitemap_title_page'),
                    'url' => $baseUrl.$route->getPath(),
                    ];
            }
        }

        return $this->render('sitemap/index.html.twig', [
            'titles' => $titles,
        ]);
    }

    #[Route('/sitemap.xml', name: 'sitemap')]
    public function generateSitemap(
        RouterInterface $router,
        #[Autowire(param: 'base_url')]
        string $baseUrl,
    ) {
        $urls = [];
        $routes = $router->getRouteCollection()->all();
        foreach ($routes as $route) {
            if (isset($route->getDefaults()['sitemap_title_page'])) {
                $urls[] = ['loc' => $baseUrl.$route->getPath()];
            }
        }

        return new Response(
            $this->renderView('sitemap/sitemap.html.twig', ['urls' => $urls]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/xml']
        );
    }
}
