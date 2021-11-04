<?php
declare(strict_types=1);

use DI\Container;
use Slim\Views\Twig;
use Slim\Flash\Messages;
use Psr\Container\ContainerInterface;
use SallePW\SlimApp\Controller\CookieMonsterController;
use SallePW\SlimApp\Controller\VisitsController;
use SallePW\SlimApp\Controller\HomeController;
use SallePW\SlimApp\Controller\FlashController;
use SallePW\SlimApp\Controller\RegisterController;
use SallePW\SlimApp\Controller\LoginController;
use SallePW\SlimApp\Controller\LogoutController;
use SallePW\SlimApp\Controller\StoreController;
use SallePW\SlimApp\Controller\ProfileController;
use SallePW\SlimApp\Controller\WalletController;
use SallePW\SlimApp\Controller\PasswordController;
use SallePW\SlimApp\Controller\MyGamesController;
use SallePW\SlimApp\Controller\FriendController;
use SallePW\SlimApp\Controller\FriendsRequestsController;
use SallePW\SlimApp\Controller\FriendsSendController;
use SallePW\SlimApp\Model\Repository\MySQLUserRepository;
use SallePW\SlimApp\Model\Repository\MySQLFriendRepository;
use SallePW\SlimApp\Model\Repository\MySQLWishlistRepository;
use SallePW\SlimApp\Model\Repository\PDOSingleton;
use SallePW\SlimApp\Controller\WishListController;
use SallePW\SlimApp\Controller\WishListGameController;



$container = new Container();

$container->set('db', function () {
    return PDOSingleton::getInstance(
        $_ENV['MYSQL_ROOT_USER'],
        $_ENV['MYSQL_ROOT_PASSWORD'],
        $_ENV['MYSQL_HOST'],
        $_ENV['MYSQL_PORT'],
        $_ENV['MYSQL_DATABASE']
    );
});

$container->set(UserRepository::class, function (ContainerInterface $container) {
    return new MySQLUserRepository($container->get('db'));
});

$container->set(FriendRepository::class, function (ContainerInterface $container) {
    return new MySQLFriendRepository($container->get('db'));
});

$container->set(WishlistRepository::class, function (ContainerInterface $container) {
    return new MySQLWishlistRepository($container->get('db'));
});

$container->set(
    'view',
    function () {
        return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
    }
);

$container->set(
    HomeController::class,
    function (ContainerInterface $c) {
        $controller = new HomeController($c->get("view"), $c->get("flash"));
        return $controller;
    }
);

$container->set(
    VisitsController::class,
    function (ContainerInterface $c) {
        $controller = new VisitsController($c->get("view"));
        return $controller;
    }
);

$container->set(
    CookieMonsterController::class,
    function (ContainerInterface $c) {
        $controller = new CookieMonsterController($c->get("view"));
        return $controller;
    }
);

$container->set(
    RegisterController::class,
    function (ContainerInterface $c) {
        $controller = new RegisterController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    LoginController::class,
    function (ContainerInterface $c) {
        $controller = new LoginController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    'flash',
    function () {
        return new Messages();
    }
);

$container->set(
    FlashController::class,
    function (Container $c) {
        $controller = new FlashController($c->get("view"), $c->get("flash"));
        return $controller;
    }
);

$container->set(
    StoreController::class,
    function (ContainerInterface $c) {
        $controller = new StoreController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class), $c->get(WishlistRepository::class));
        return $controller;
    }
);

$container->set(
    MyGamesController::class,
    function (ContainerInterface $c) {
        $controller = new MyGamesController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    ProfileController::class,
    function (ContainerInterface $c) {
        $controller = new ProfileController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    LogoutController::class,
    function (ContainerInterface $c) {
        $controller = new LogoutController($c->get("view"), $c->get("flash"));
        return $controller;
    }
);

$container->set(
    WalletController::class,
    function (ContainerInterface $c) {
        $controller = new WalletController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    PasswordController::class,
    function (ContainerInterface $c) {
        $controller = new PasswordController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    WishListController::class,
    function (ContainerInterface $c) {
        $controller = new WishListController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class), $c->get(WishlistRepository::class));
        return $controller;
    }
);

$container->set(
    FriendController::class,
    function (ContainerInterface $c) {
        $controller = new FriendController($c->get("view"), $c->get("flash"), $c->get(FriendRepository::class), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    WishListGameController::class,
    function (ContainerInterface $c) {
        $controller = new WishListGameController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class), $c->get(WishlistRepository::class));
        return $controller;
    }
);

$container->set(
    FriendsRequestsController::class,
    function (ContainerInterface $c) {
        $controller = new FriendsRequestsController($c->get("view"), $c->get("flash"), $c->get(FriendRepository::class), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    FriendsSendController::class,
    function (ContainerInterface $c) {
        $controller = new FriendsSendController($c->get("view"), $c->get("flash"), $c->get(UserRepository::class), $c->get(FriendRepository::class));
        return $controller;
    }
);