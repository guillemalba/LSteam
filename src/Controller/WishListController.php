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


final class WishListController {
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

    /* Returns the list of favs added */
    public function showList(Request $request, Response $response): Response {

        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $apiUrl = "https://www.cheapshark.com/api/1.0/deals";
        $client = new Client();
        $responseAPI = $client->request('GET', $apiUrl);
        $results = json_decode($responseAPI->getBody()->getContents(), true);

        $this->data['games'] = $results;

        $myGames['games'] = $this->wishlistRepository->getWishedGames($_SESSION['username']);

        $j = 0;
        $number_items = 0;
        foreach ($myGames['games'] as $game_id) {
            $i = 0;
            foreach ($this->data['games'] as $item) {
                if ($item['gameID'] == $game_id['game_id']) {
                    $this->myData[$j]['thumb'] = $this->data['games'][$i]['thumb'];
                    $this->myData[$j]['title'] = $this->data['games'][$i]['title'];
                    $this->myData[$j]['gameID'] = $game_id['game_id'];
                    $this->myData[$j]['normalPrice'] = $this->data['games'][$i]['normalPrice'];
                    $number_items++;
                }
                $i++;
            }
            $j++;
        }

        if ($number_items == 0) {
            $this->errors['error'] = 'You have no games added to the wishlist yet, you can browse games to either buy or to add them to their wishlist from the store.';
        }

        $this->myData['games'] = $this->myData;
        $this->myData['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        return $this->twig->render(
            $response,
            'wishlist.twig',
            [
                'formErrors' => $this->errors,
                'formData' => $this->myData
            ]
        );
    }

    /* Deletes a game from the list */
    public function deleteFromWishList (Request $request, Response $response): Response {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        $userAccepted = $request->getAttribute('gameId');

        $this->wishlistRepository->removeWishedGame($_SESSION['username'], $userAccepted);


    }

}