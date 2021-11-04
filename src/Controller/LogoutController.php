<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;


final class LogoutController{
    private Twig $twig;

    private Messages $flash;

    public function __construct(Twig $twig, Messages $flash) {
        $this->twig = $twig;
        $this->flash = $flash;
    }

    public function logout(Request $request, Response $response):Response {
        
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        session_destroy();
        unset($_SESSION['username']);
        unset($_SESSION['activated']);
        
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $response
                ->withHeader('Location', $routeParser->urlFor('home'))
                ->withStatus(302);
    }
}