<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use SallePW\SlimApp\Model\UserRepository;

final class LoginController {
    
    private Twig $twig;
    private UserRepository $userRepository;
    private Messages $flash;

    public function __construct(Twig $twig, Messages $flash, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
    }

    public function showForm(Request $request, Response $response): Response {

        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'login.twig'
        );

    }
    public function handleFormSubmission(Request $request, Response $response): Response {
        $ok = true;

        try {
            $data = $request->getParsedBody();
            $errors = [];

            if (!empty($_POST["username"]) && !empty($_POST["pass"])) {
                $error = false;
                $username = $_POST['username'];
                $pass = $_POST['pass'];
                
                if (strpos($username, '@') !== false) {
                    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) { 
                        $ok = false;
                    }
                    if(!str_ends_with($username, '@salle.url.edu')){
                        $ok = false;
                    }
                } else {

                    if (!preg_match('/^[a-zA-Z0-9]+$/',$username)) {
                        $ok = false;
                    }
                }

                if (strlen($pass) < 6){
                    $ok = false;
                }

                if (!preg_match('/^[a-zA-Z0-9]+$/',$pass)) {
                    $ok = false;
                }

                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

                if ($ok == true) {
                    $user_exist = $this->userRepository->checkUser($username);

                    if ($user_exist != 0) {
                        $pass_exist = $this->userRepository->checkPass($pass, $username);

                        if ($pass_exist != 0) {
                            $ok = true;
                        } else {
                            $ok = false;
                        }
                    } else {
                        $ok = false;
                    }
                }  
                if ($ok == false) {
                    $errors['pass'] = 'Username or password are incorrect';
                } else {
                    $_SESSION['activated'] = $this->userRepository->checkActivation($this->userRepository->getToken($username));

                    if ($_SESSION['activated'] == 0) {
                        $errors['activated'] = 'You have to activate your account first';
                        $ok = false;
                    } else {
                        $ok = true;
                    }
                }
                
            }    
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if ($ok == true) {
            $_SESSION['username'] = $this->userRepository->getUsername($username);
            
            return $response
                ->withHeader('Location', $routeParser->urlFor('store'))
                ->withStatus(302);
        }

        return $this->twig->render(
            $response,
            'login.twig',
            [
                'formErrors' => $errors,
                'formData' => $data
            ]
        );
    }
        
}