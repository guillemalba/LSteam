<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Model\WishlistRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;
use SallePW\SlimApp\Model\Game;
use GuzzleHttp\Client;
use SallePW\SlimApp\Model\UserRepository;


final class StoreController {
    private Game $game;
    private Twig $twig;
    private Messages $flash;
    private UserRepository $userRepository;
    private WishlistRepository $wishlistRepository;
    private $data = [];

    public function __construct(Twig $twig, Messages $flash, UserRepository $userRepository, WishlistRepository $wishlistRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
        $this->wishlistRepository = $wishlistRepository;
    }

    public function showGames(Request $request, Response $response): Response {
        
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        $apiUrl = "https://www.cheapshark.com/api/1.0/deals";
        $client = new Client();
        $responseAPI = $client->request('GET', $apiUrl);
        $results = json_decode($responseAPI->getBody()->getContents(), true);

        $this->data['games'] = $results;

        return $this->twig->render(
            $response,
            'store.twig',
            [
                'notifications' => $notifications,
                'formData' => $this->data,
            ]
        );
    }

    public function buyGame (Request $request, Response $response, $args): Response {
        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        $gameId = $request->getAttribute('gameId');

        $apiUrl = "https://www.cheapshark.com/api/1.0/deals";
        $client = new Client();
        $responseAPI = $client->request('GET', $apiUrl);
        $results = json_decode($responseAPI->getBody()->getContents(), true);

        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);
        $this->data['games'] = $results;

        $posicio =  0;

        $i = 0;
        
        foreach ($this->data['games'] as $item) {
            if ($item['gameID'] == $gameId) {
                $posicio = $i;
            }
            $i++;
        }
        
        $priceGame = $this->data['games'][$posicio]['normalPrice'];

        if ($this->userRepository->checkMoney($_SESSION['username']) >= $priceGame) {
            // add gameID to owned games on DB
            // if is 'null', db is empty and we insert the first gameID
            // else, we get the gameIDs string and concatenate a new one
            if ($this->userRepository->getGames($_SESSION['username']) == 'null') {
                $this->userRepository->saveGame($_SESSION['username'], $gameId);
                $this->userRepository->addMoney($_SESSION['username'], -1 * $priceGame);

            } else {
                $full_ids = $this->userRepository->getGames($_SESSION['username']);
                $full_ids .= $gameId;
                $this->userRepository->saveGame($_SESSION['username'], $full_ids);
                $this->userRepository->addMoney($_SESSION['username'], -1 * $priceGame);
            }
            $this->flash->addMessage('notifications', 'Bought Succesful!');
            // si id esta a la wishlist, eliminal


        } else {
            $this->flash->addMessage('notifications', 'You do not have enough money in the wallet');
        }

        //NO FA LA REDIRECCIO
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $response
            ->withHeader('Location', $routeParser->urlFor('store'))
            ->withStatus(302);
    }

    /* Add a game into the wishlist */
    public function addToWishList (Request $request, Response $response, $args) {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        $gameId = $request->getAttribute('gameId');

        $this->wishlistRepository->saveWishedGame($_SESSION['username'], $gameId);

        $this->flash->addMessage('notifications', 'Item added to Wish List!');

        return $response
            ->withHeader('Location', $routeParser->urlFor('store'))
            ->withStatus(302);

    }

}