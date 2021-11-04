<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Flash\Messages;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Ramsey\Uuid\Uuid;

final class ProfileController {

    private const UPLOADS_DIR = __DIR__ . '/../../public/assets/uploads';
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];
    private const UNEXPECTED_ERROR = "Se produjo un error inesperado al cargar la imagen '%s'";
    private const INVALID_EXTENSION_ERROR = "La extensión de la imagen recivida  '%s' no es válida";

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

        $notifications = $messages['notifications'] ?? [];

        $this->data['username'] = $_SESSION['username'];
        $this->data['email'] = $this->userRepository->getEmail2($_SESSION['username']);
        $this->data['birthday'] = $this->userRepository->getBirthday($_SESSION['username']);
        $this->data['phone'] = $this->userRepository->getPhone($_SESSION['username']);
        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);
        if (($this->data['phone']) == "0") {
            $this->data['phone'] = "";
        }

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'notifications' => $notifications,
                'formData' => $this->data
            ]
        );

    }

    public function uploadFileAction(Request $request, Response $response): Response {
        $ok = true;
        $hayFoto = false;

        $this->data['username'] = $_SESSION['username'];
        $this->data['email'] = $this->userRepository->getEmail2($_SESSION['username']);
        $this->data['birthday'] = $this->userRepository->getBirthday($_SESSION['username']);
        $this->data['phone'] = $this->userRepository->getPhone($_SESSION['username']);
        $this->data['photo'] = $this->userRepository->getPicture($_SESSION['username']);
        if (($this->data['phone']) == "0") {
            $this->data['phone'] = "";
        }

        if (isset($_POST['upload']) && $_POST['upload'] == 'Update') {

            $phone = $_POST['phone'];

            $name = $_FILES['fileToUpload']['name'];
            $fileType = $_FILES['fileToUpload']['type'];
            $filePath = $_FILES['fileToUpload']['tmp_name'];
            if (!file_exists(self::UPLOADS_DIR.$_FILES["fileToUpload"]["name"])) {

                list($width, $height) = getimagesize($filePath);
                $fileNameCmps = explode(".", $name);
                $fileExtension = strtolower(end($fileNameCmps));

                if (!isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
                    $ok = false;
                    $this->errors['image'] = sprintf(self::UNEXPECTED_ERROR, $_FILES['fileToUpload']['name']);
                }

                if ($_FILES['fileToUpload']['size'] > 1000000) {
                    $ok = false;
                    $this->errors['image'] = "La mida de esta imagen en mayor a 1MB";
                }

                if (!$this->isValidFormat($fileExtension)) {
                    $ok = false;
                    $this->errors['image'] = sprintf(self::INVALID_EXTENSION_ERROR, $fileExtension);
                }

                if ($width > 500 && $height > 500) {
                    $ok = false;
                    $this->errors['image'] = "Las dimensiones de la imagen deben ser 500x500 o menor.";
                }
                $hayFoto = true;
            }

            $phone_ok = true;

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
            if($phone_ok == false){
                $this->errors['phone'] = 'El numero de telefono no es correcto';
                $ok = false;
            }

            if ($ok == true) {
                $myuuid = Uuid::uuid4();
                if ($hayFoto == true) {
                    move_uploaded_file($filePath, self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $myuuid . "." . $fileExtension);
                    $this->userRepository->addPicture($_SESSION['username'], $myuuid->toString() . "." . $fileExtension);
                }

                $this->userRepository->addPhone($_SESSION['username'], $phone);

            }
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        if ($ok == true) {
            return $response
                ->withHeader('Location', $routeParser->urlFor('profile'))
                ->withStatus(302);
        }

        return $this->twig->render(
            $response,
            'profile.twig',
            [
                'formErrors' => $this->errors,
                'formData' => $this->data
            ]
        );

    }

    private function isValidFormat(string $extension): bool {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }
}
