<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use SallePW\SlimApp\Model\FriendRepository;
use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class FriendsSendController {
    private Twig $twig;
    private Messages $flash;
    private User $user;
    private UserRepository $userRepository;
    private FriendRepository $friendRepository;
    private $data = [];
    private $errors = [];

    public function __construct(Twig $twig, Messages $flash, UserRepository $userRepository, FriendRepository $friendRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
        $this->friendRepository = $friendRepository;
    }

    public function showForm(Request $request, Response $response): Response
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        return $this->twig->render(
            $response,
            'friendSend.twig',
            [
                'notifications' => $notifications,
                'formData' => $this->data
            ]
        );
    }

    public function sendRequest(Request $request, Response $response): Response {
        $ok = true;
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $error = false;
  
        try{
            $this->data = $request->getParsedBody();

            $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);

            $myUser = $this->userRepository->getId($_SESSION['username']);
            

            if($this->data['username'] == $_SESSION['username']){
                $ok = false;
                $this->errors['error'] = 'You can not be your own friend!';
                $error = true;
            }

            else if($this->userRepository->checkUser($this->data['username']) < 1){
                $ok = false;
                $this->errors['error'] = 'This user does not exists';
                $error = true;
            }

            if($this->friendRepository->checkSend($this->userRepository->getId($_SESSION['username']), $this->userRepository->getId($this->data['username'])) == 1){
                $ok = false;
                $this->errors['error'] = 'You have allready send a request to this user';
                $error = true;
            }

            if($this->data['username'] == ""){
                $ok = false;
                $this->errors['error'] = 'The user name has to be filled in';
                $error = true;
            }

            if (!$error){
                $idFriend = $this->userRepository->getId($this->data['username']);
                $this->friendRepository->save($myUser, $idFriend, 0);
            }

        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        if ($ok == true) {            
            return $response
                ->withHeader('Location', $routeParser->urlFor('store'))
                ->withStatus(302);
        }

        return $this->twig->render(
            $response,
            'friendSend.twig',
            [
                'formErrors' => $this->errors,
                'formData' => $this->data
            ]
        );

    }
}