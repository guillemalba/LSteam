<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class WalletController {
    private Twig $twig;
    private Messages $flash;
    private User $user;
    private UserRepository $userRepository;
    private $data = [];
    private $errors = [];
    private $ok = [];

    public function __construct(Twig $twig, Messages $flash, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
    }

    public function apply(Request $request, Response $response): Response
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);
        $this->data['wallet'] = $this->userRepository->checkMoney($_SESSION['username']);

        return $this->twig->render(
            $response,
            'wallet.twig',
            [
                'formData' => $this->data
            ],
            ['notifications' => $notifications]
        );
    }

    public function handleFormSubmission(Request $request, Response $response): Response {
  
        try{
            $data = $request->getParsedBody();

            $data['photo'] = $this->userRepository->getPicture($_SESSION['username']);
            

            if($data['amount'] == null || $data['amount'] <= 0){
                $this->errors['amount'] = 'La cantidad de dinero tiene que ser positivo';
            }else{
                $amount = $_POST['amount'];
                $this->userRepository->addMoney($_SESSION['username'], floatval($amount));
                $this->ok['ok'] = 'Se han añadido '.$amount.'€ en tu wallet';
            }

            $data['wallet'] = $this->userRepository->checkMoney($_SESSION['username']);

        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        return $this->twig->render(
            $response,
            'wallet.twig',
            [
                'formErrors' => $this->errors,
                'formOk' => $this->ok,
                'formData' => $data
            ]
        );
    }
}