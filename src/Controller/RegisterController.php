<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;
use DateTime;

final class RegisterController {

    private Twig $twig;
    private UserRepository $userRepository;
    private Messages $flash;
    private $errors = [];
    private User $user;
    private String $username;

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
            'register.twig',
            [
                'formAction' => $routeParser->urlFor("handle-form"),
                'formMethod' => "POST"
            ],
            ['notifications' => $notifications]
        );
    }

    public function handleFormSubmission(Request $request, Response $response): Response {
        $ok = true;
        try {
            $data = $request->getParsedBody();

            date_default_timezone_set('Europe/Madrid');
            $date = date("Y-m-d");

            if(!empty($data['email']) && !empty($data['username']) && !empty($data['pass']) && !empty($data['pass2']) && !empty($data['birthday'])){

                $this->username = $_POST['username'];
                $email = $_POST['email'];
                $pass = $_POST['pass'];
                $pass2 = $_POST['pass2'];
                $birthday = $_POST['birthday'];
                $phone = $_POST['phone'];

                $numUsers = $this->userRepository->checkUserRegister($this->username);
                $numEmail = $this->userRepository->checkEmailRegister($email);

                $datefrom = strtotime($birthday, 0);
                $dateto = strtotime($date, 0);
                $difference = $dateto - $datefrom;


                $phone_ok = true;

                //Comprovacio si mobil si hi ha
                if($phone != ''){
                    if($phone[0] == '+' && $phone[1] == '3' && $phone[2] == '4'){
                        $phone = str_replace('+34', '', $phone);
                    }

                    if($phone[0] > 7 || $phone[0] < 6){
                        $phone_ok = false;
                    }
                }else{
                    $phone = "0";
                }

                //Comprovacions
                if (!preg_match('/^[a-zA-Z0-9]+$/',$this->username)) {
                    $this->errors['username'] = 'El usuario debe ser alfanumérico';
                    $ok=false;
                }
                if($numUsers != 0){
                    $this->errors['username'] = 'Ya hay un usuario registrado con este username';
                    $ok=false;
                }
                if($phone_ok == false){
                    $this->errors['phone'] = 'El numero de telefono no es correcto';
                    $ok = false;
                }
                if(!preg_match("/[0-9]/",$pass)){
                    $this->errors['pass'] = 'La contraseña debe contener como mínimo un número';
                    $ok=false;
                }
                if(!preg_match('/[A-Z]/',$pass) || !preg_match('/[a-z]/',$pass)){
                    $this->errors['pass'] = 'La contraseña debe contener como mínimo una letra minúscula y una mayúscula';
                    $ok=false;
                }
                if(strlen($pass) < 6){
                    $this->errors['pass'] = 'La contraseña debe de ser de minimo 6 caracteres de largo';
                    $ok=false;
                }
                if($pass != $pass2){
                    $this->errors['pass'] = 'Las contraseñas no coinciden';
                    $ok = false;
                }
                if($numEmail != 0){
                    $this->errors['email'] = 'Ya hay un usuario registrado con este email';
                    $ok=false;
                }
                if(!str_ends_with($email, '@salle.url.edu')){
                    $this->errors['email'] = 'El email debe pertenecer al dominio @salle.url.edu';
                    $ok=false;
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
                    $this->errors['email'] = 'Formato de email incorrecto';
                    $ok = false;
                }
                if ($this->validateDate($birthday) == false) {
                    $this->errors['birthday'] = 'Formato de fecha incorrecto';
                    $ok=false;
                } else {
                    if($difference/(3600*24*365.25) < 18){
                        $this->errors['birthday'] = 'Para hacer una cuenta debes ser mayor de edad';
                        $ok=false;
                    }
                }
                
                if($ok == true){

                    $real_date = DateTime::createFromFormat ( 'Y-m-d' , $birthday); 

                    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

                    do{
                        $token = rand(1, 2000000000);
                    }while (!$this->userRepository->checkToken($token));

                    $this->user = new User($this->username, $email, $hashed_pass, $real_date, $phone, $token, 0.0, 0, "default_picture.jpg");
                    $this->userRepository->save($this->user);

                    $this->sendEmailActivate($email, $token);
                }
            }
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if ($ok == true) {
            return $response
                ->withHeader('Location', $routeParser->urlFor('login'))
                ->withStatus(302);
        }

        return $this->twig->render(
            $response,
            'register.twig',
            [
                'formErrors' => $this->errors,
                'formData' => $data,
                'formAction' => $routeParser->urlFor("handle-form"),
                'formMethod' => "POST"
            ]
        );
    }

    public function sendEmailActivate (string $email, int $token) {
        $mail = new PHPMailer(true);
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'30/activate?token='.$token;

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                //Enable verbose debug output
            $mail->isSMTP();                                      //Send using SMTP
            $mail->Host = 'mail.smtpbucket.com';            //Set the SMTP server to send through
            $mail->Port = 8025;                              //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('example@lsteam.com', 'LSteam');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Activation link';
            $mail->Body    = '<div>To activate your user, please click the <b>following link</b>:<br>'.$url.'<br>After you activate, you will receive another email with your login</div>';
            $mail->AltBody = 'To activate your user, please click the following link:\n'.$url.'\nAfter you activate, you will receive another email with your login';
            $mail->send();
            echo '<h1>Activation link has been sent to your e-mail!</h1>';
            echo '<a class="boton_head" href="https://www.smtpbucket.com/emails?sender=example@lsteam.com&recipient=' . $email . '">Go to your email</a>';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function sendEmailLogin (string $email) {
        $mail = new PHPMailer(true);
        $url = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'30/login';

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                //Enable verbose debug output
            $mail->isSMTP();                                      //Send using SMTP
            $mail->Host = 'mail.smtpbucket.com';            //Set the SMTP server to send through
            $mail->Port = 8025;                              //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('example@lsteam.com', 'LSteam');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Login button';
            $mail->Body = '<div>Thanks for signing up!<br> Please click the button below to login.                 
                    <button name="inciar" type="button";><a style="color: black; text-decoration: none;" href="'.$url.'">----> Log In </a></button>
                    </div>';
            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function addDB (Request $request, Response $response): Response
    {
        $messages = $this->flash->getMessages();

        $notifications = $messages['notifications'] ?? [];

        $token = intval($_GET['token']);
        if ($this->userRepository->checkActivation($token) == 0) {
            echo '<h1>Se ha enviado un correo para iniciar sesión</h1>';
            $this->sendEmailLogin($this->userRepository->getEmail($token));
            $_SESSION['token'] = $token;
            $_SESSION['activated'] = 1;
            $this->userRepository->addMoney($this->userRepository->getUsernameToken($token), 50.0);
            $this->userRepository->activateUser($this->userRepository->getUsernameToken($token));
        } else {
            echo '<h1>Este usuario ya ha confirmado su sesion</h1>';
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        return $this->twig->render(
            $response,
            'register.twig',
            [
                'formAction' => $routeParser->urlFor("handle-form"),
                'formMethod' => "GET"
            ],
            ['notifications' => $notifications]
        );
    }

    public function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}