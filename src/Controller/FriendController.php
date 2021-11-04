<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Model\FriendRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use SallePW\SlimApp\Model\Game;
use SallePW\SlimApp\Model\UserRepository;

final class FriendController
{
    private Friend $friend;
    private Twig $twig;
    private Messages $flash;
    private UserRepository $userRepository;
    private FriendRepository $friendRepository;


    public function __construct(Twig $twig, Messages $flash, FriendRepository $friendRepository, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->friendRepository = $friendRepository;
        $this->userRepository = $userRepository;
    }

    public function showFriends(Request $request, Response $response): Response
    {

        $messages = $this->flash->getMessages();
        $notifications = $messages['notifications'] ?? [];

        $myUser = $this->userRepository->getId($_SESSION['username']);
        $data2['friends'] = $this->friendRepository->getAllFriends($myUser);

        foreach ($data2['friends'] as $friend) {
            if ($friend['friend2'] == $myUser) {
                $this->data['friends'][] = array(
                    'username' => $this->userRepository->getUsername2(intval($friend['friend1'])),
                    'became_friends' => $friend['accept_date']
                );

            } else {
                $this->data['friends'][] = array(
                    'username' => $this->userRepository->getUsername2(intval($friend['friend2'])),
                    'became_friends' => $friend['accept_date']
                );
            }
        }

        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        return $this->twig->render(
            $response,
            'friends.twig',
            [
                'notifications' => $notifications,
                'formData' => $this->data,
            ]
        );
    }
}