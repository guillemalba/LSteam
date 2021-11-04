<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Middleware;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteContext;


final class BeforeMiddleware {

    public function __invoke(Request $request, RequestHandler $next): Response {

        if(!isset($_SESSION['username']) || $_SESSION['activated'] == 0) {
            $response = new SlimResponse();
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();

            return $response
                ->withHeader('Location', $routeParser->urlFor('login'))
                ->withStatus(302);
        }
        return $response = $next->handle($request);
    }
}