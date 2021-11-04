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
use SallePW\SlimApp\Model\Repository;
use GuzzleHttp\Client;
use SallePW\SlimApp\Model\UserRepository;


final class WishListGameController {
    private Game $game;
    private Twig $twig;
    private Messages $flash;
    private UserRepository $userRepository;
    private WishlistRepository $wishlistRepository;
    private $data = [];
    private $myData = array(array());
    private $errors = [];


    public function __construct(Twig $twig, Messages $flash, UserRepository $userRepository, WishlistRepository $wishlistRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
        $this->wishlistRepository = $wishlistRepository;
    }

    /* Shows the information of a favorite game */
    public function showWishlistGame(Request $request, Response $response, $args): Response {

        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $apiUrl = "https://www.cheapshark.com/api/1.0/deals";
        $client = new Client();
        $responseAPI = $client->request('GET', $apiUrl);
        $results = json_decode($responseAPI->getBody()->getContents(), true);

        $this->data['games'] = $results;

        $userAccepted = $request->getAttribute('gameId');

        $myGames['games'] = $this->wishlistRepository->getWishedGames($_SESSION['username']);
        
        $found = 0;
        foreach ($myGames['games'] as $my_game_id) {
            $i = 0;
            if ($my_game_id['game_id'] == $userAccepted) {
                foreach ($this->data['games'] as $item) {
                    if ($item['gameID'] == $my_game_id['game_id']) {
                        $this->myData[0]['thumb'] = $this->data['games'][$i]['thumb'];
                        $this->myData[0]['title'] = $this->data['games'][$i]['title'];
                        $this->myData[0]['gameID'] = $my_game_id['game_id'];
                        $this->myData[0]['normalPrice'] = $this->data['games'][$i]['normalPrice'];
                        $found++;
                    }
                    $i++;
                }
            }
        }

        if ($found == 0) {
            $this->errors['error'] = 'This item is not added to the wishlist yet, you can browse games to either buy or to add them to their wishlist from the store.';
        }

        $this->myData['games'] = $this->myData;
        $this->myData['photo'] = $this->userRepository->getPicture($_SESSION['username']);
        
        return $this->twig->render(
            $response,
            'wishlist_game.twig',
            [
                'formErrors' => $this->errors,
                'formData' => $this->myData,
                'formMethod' => "GET"
            ]
        );

    }

}