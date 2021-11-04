<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;
use SallePW\SlimApp\Model\Game;
use SallePW\SlimApp\Model\Repository;
use GuzzleHttp\Client;
use SallePW\SlimApp\Model\UserRepository;


final class MyGamesController {
    private Game $game;
    private Twig $twig;
    private Messages $flash;
    private UserRepository $userRepository;
    private $myGames = [];
    private $positions = [];
    private $myData = array(array());
    private $errors = [];

    public function __construct(Twig $twig, Messages $flash, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
    }

    public function showMyGames(Request $request, Response $response): Response {

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $apiUrl = "https://www.cheapshark.com/api/1.0/deals";
        $client = new Client();
        $responseAPI = $client->request('GET', $apiUrl);
        $results = json_decode($responseAPI->getBody()->getContents(), true);

        $this->data['games'] = $results;

        $myGames = explode("-", $this->userRepository->getGames($_SESSION['username']));
        $number_items = 0;
        $j = 0;
        foreach ($myGames as $game_id) {
            $i = 0;
            foreach ($this->data['games'] as $item) {
                if ($item['gameID'] == $game_id) {
                    $this->myData[$j]['thumb'] = $this->data['games'][$i]['thumb'];
                    $this->myData[$j]['title'] = $this->data['games'][$i]['title'];
                    $this->myData[$j]['gameID'] = $game_id;
                    $this->myData[$j]['normalPrice'] = $this->data['games'][$i]['normalPrice'];
                    $number_items++;
                }
                $i++;
            }
            $j++;
        }

        if ($number_items == 0) {
            $this->errors['error'] = 'You have no owned games yet, you can browse games to either buy or to add them to their wishlist from the store.';
        }

        $this->myData['games'] = $this->myData;
        $this->myData['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        return $this->twig->render(
            $response,
            'my_games.twig',
            [
                'formErrors' => $this->errors,
                'formData' => $this->myData,
                'formAction' => $routeParser->urlFor("my_games"),
                'formMethod' => "GET"
            ]
        );
        
    }

}