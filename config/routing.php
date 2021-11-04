<?php
declare(strict_types=1);

use SallePW\SlimApp\Controller\HomeController;
use SallePW\SlimApp\Controller\VisitsController;
use SallePW\SlimApp\Middleware\StartSessionMiddleware;
use SallePW\SlimApp\Middleware\BeforeMiddleware;
use SallePW\SlimApp\Controller\CookieMonsterController;
use SallePW\SlimApp\Controller\FlashController;
use SallePW\SlimApp\Controller\RegisterController;
use SallePW\SlimApp\Controller\LoginController;
use SallePW\SlimApp\Controller\LogoutController;
use SallePW\SlimApp\Controller\StoreController;
use SallePW\SlimApp\Controller\ProfileController;
use SallePW\SlimApp\Controller\WalletController;
use SallePW\SlimApp\Controller\PasswordController;
use SallePW\SlimApp\Controller\MyGamesController;
use SallePW\SlimApp\Controller\WishListController;
use SallePW\SlimApp\Controller\WishListGameController;
use SallePW\SlimApp\Controller\FriendController;
use SallePW\SlimApp\Controller\FriendsRequestsController;
use SallePW\SlimApp\Controller\FriendsSendController;

$app->add(StartSessionMiddleware::class);

$app->get(
    '/',
    HomeController::class . ':apply')
    ->setName('home');

$app->get(
    '/visits',
    VisitsController::class . ":showVisits")
    ->setName('visits')->add(BeforeMiddleware::class);

$app->get(
    '/cookies',
    CookieMonsterController::class . ":showAdvice")   
    ->setName('cookies')->add(BeforeMiddleware::class);

$app->get(
    '/flash',
    FlashController::class . ":addMessage")
    ->setName('flash')->add(BeforeMiddleware::class);

$app->get(
    '/register',
    RegisterController::class . ":showForm")
    ->setName('register');
$app->post(
    '/register',
    RegisterController::class . ":handleFormSubmission")
    ->setName('handle-form');

$app->get(
    '/login',
    LoginController::class . ":showForm")   
    ->setName('login');

$app->post(
    '/login',
    LoginController::class . ":handleFormSubmission")
    ->setName('handle-login');

$app->get(
    '/activate',
    RegisterController::class . ":addDB")   
    ->setName('activate_token');

$app->post(
    '/logout',
    LogoutController::class . ":logout")   
    ->setName('logout');

$app->get(
    '/store',
    StoreController::class . ':showGames')
    ->setName('store')->add(BeforeMiddleware::class);

$app->get(
    '/user/mygames',
    MyGamesController::class . ":showMyGames")   
    ->setName('my_games')->add(BeforeMiddleware::class);

$app->post(
    '/store/buy/{gameId}',
    StoreController::class . ":buyGame")
    ->setName('buy_game');

$app->get(
    '/profile',
    ProfileController::class . ":showForm")
    ->setName('profile')->add(BeforeMiddleware::class);

$app->post(
    '/profile',
    ProfileController::class . ":uploadFileAction")
    ->setName('picture_profile');

$app->get(
    '/profile/changePassword',
    PasswordController::class . ":showForm")
    ->setName('changePassword')->add(BeforeMiddleware::class);

$app->post(
    '/profile/changePassword',
    PasswordController::class . ":uploadPassword")
    ->setName('newPassword');

$app->get(
    '/user/wishlist',
    WishListController::class . ":showList")
    ->setName('showList')->add(BeforeMiddleware::class);

$app->get(
    '/user/wishlist/{gameId}',
    WishListGameController::class . ":showWishlistGame")
    ->setName('showWishlistGame')->add(BeforeMiddleware::class);

$app->post(
    '/user/wishlist/{gameId}',
    StoreController::class . ":addToWishList")
    ->setName('addToWishList')->add(BeforeMiddleware::class);

$app->delete(
    '/user/wishlist/{gameId}',
    WishListController::class . ":deleteFromWishList")
    ->setName('deleteFromWishList')->add(BeforeMiddleware::class);

$app->get(
    '/user/wallet',
    WalletController::class . ":apply")
    ->setName('wallet')->add(BeforeMiddleware::class);

$app->post(
    '/user/wallet',
    WalletController::class . ":handleFormSubmission")
    ->setName('handle-wallet');

$app->get(
    '/user/friends',
    FriendController::class . ":showFriends")
    ->setName('friends')->add(BeforeMiddleware::class);

$app->get(
    '/user/friendRequests',
    FriendsRequestsController::class . ":showRequests")
    ->setName('requests')->add(BeforeMiddleware::class);

$app->delete(
    '/user/friendRequests',
    FriendsRequestsController::class . ":deleteRequests")
    ->setName('deleteRequests');

$app->post(
    '/user/friendRequests/accept/{requestId}',
    FriendsRequestsController::class . ":acceptRequests")
    ->setName('acceptRequests');

$app->get(
    '/user/friendRequests/send',
    FriendsSendController::class . ":showForm")
    ->setName('send')->add(BeforeMiddleware::class);

$app->post(
    '/user/friendRequests/send',
    FriendsSendController::class . ":sendRequest")
    ->setName('handle-friendRequest');
