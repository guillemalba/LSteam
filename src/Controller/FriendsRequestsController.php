<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Model\FriendRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;
use SallePW\SlimApp\Model\Game;
use SallePW\SlimApp\Model\Repository;
use SallePW\SlimApp\Model\UserRepository;

final class FriendsRequestsController {
    private Friend $friend;
    private Twig $twig;
    private Messages $flash;
    private UserRepository $userRepository;
    private FriendRepository $friendRepository;
    private $errors = [];


    public function __construct(Twig $twig, Messages $flash, FriendRepository $friendRepository, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->friendRepository = $friendRepository;
        $this->userRepository = $userRepository;
    }

    public function showRequests(Request $request, Response $response): Response {
        $messages = $this->flash->getMessages();


        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $myUser = $this->userRepository->getId($_SESSION['username']);
        $data2['friends'] = $this->friendRepository->getAllFriendsRequests($myUser);
        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);
        if($data2['friends'] == null){
            $this->errors['error'] = 'You do not have any friends request';
        }else{
            foreach ($data2['friends'] as $friend) {
                $this->data['friends'][] = array(
                    'username' => $this->userRepository->getUsername2(intval($friend['friend1'])),
                    'id'=> $friend['friend1']
                );
            }
        }

        return $this->twig->render(
            $response,
            'friendsRequests.twig',
            [
                'formErrors' => $this->errors,
                'formData' => $this->data
            ]
        );
    }

    public function acceptRequests (Request $request, Response $response): Response {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $userAccepted = $request->getAttribute('requestId');

        $myUser = $this->userRepository->getId($_SESSION['username']);
        $this->friendRepository->acceptedRequest(intval($userAccepted), $myUser);


        return $response
            ->withHeader('Location', $routeParser->urlFor('requests'))
            ->withStatus(302);
    }

    public function deleteRequests (Request $request, Response $response): Response {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $myUser = $this->userRepository->returnId($_SESSION['username']);

        $userRemove = $this->getRequest()->getPost('remove_request');
        print_r(intval($userRemove));
        $this->friendRepository->removeRequest(intval($userRemove), $myUser);

        return $response
            ->withHeader('Location', $routeParser->urlFor('requests'))
            ->withStatus(302);
    }
}