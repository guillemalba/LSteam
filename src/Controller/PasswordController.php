<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;

final class PasswordController {

    private Twig $twig;
    private Messages $flash;
    private UserRepository $userRepository;
    private $data = [];
    private $errors = [];

    public function __construct(Twig $twig, Messages $flash, UserRepository $userRepository) {
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
    }

    public function showForm(Request $request, Response $response): Response {

        $messages = $this->flash->getMessages();

        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        $notifications = $messages['notifications'] ?? [];

        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        return $this->twig->render(
            $response,
            'password.twig',
            [
                'notifications' => $notifications,
                'formData' => $this->data
            ]
        );

    }

    public function uploadPassword(Request $request, Response $response): Response {
        $ok = true;
        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);

        try {
            $data2 = $request->getParsedBody();
            if(!empty($data2['old_password']) && !empty($data2['new_password']) && !empty($data2['confirm_password'])) {

                $old_password = $_POST['old_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                $pass_igual = $this->userRepository->checkPass($old_password, $_SESSION['username']);

                if ($pass_igual == 0) {
                    $ok = false;
                }
                if(!preg_match("/[0-9]/",$new_password)){
                    $ok=false;
                }
                if(!preg_match('/[A-Z]/',$new_password) || !preg_match('/[a-z]/',$new_password)){
                    $ok=false;
                }
                if(strlen($new_password) < 6){
                    $ok=false;
                }
                if($new_password != $confirm_password){
                    $ok = false;
                }

                if ($ok == true) {
                    $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
                    $this->userRepository->updatePassword($_SESSION['username'], $hashed_pass);
                    $this->errors['ok'] = 'The password has been updated successfully';
                }
                else {
                    $this->errors['password'] = 'There is an error in any of the fields';
                }
            }
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        return $this->twig->render(
            $response,
            'password.twig',
            [
                'formData' => $this->data,
                'formErrors' => $this->errors
            ]
        );
    }
}
