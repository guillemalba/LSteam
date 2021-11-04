<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

interface UserRepository {
    public function save(User $user): void;

    public function checkUserRegister(String $username):int;

    public function checkEmailRegister(String $email):int;

    public function checkUser(String $user):int;

    public function checkPass(String $pass, String $user):int;

    public function checkToken(int $token): bool;

    public function getUsername(String $user): String;

    public function getUsername2(int $id): String;

    public function getEmail(int $token): String;

    public function getId(String $user): int;

    public function getEmail2(String $user): String;

    public function getBirthday(String $user): String;

    public function getPhone(String $user): String;

    public function activateUser(String $username): void;

    public function checkActivation(int $token):int;

    public function checkActivation2(String $username):int;

    public function checkMoney(String $username): float;

    public function addMoney(String $username, float $amount): void;

    public function addPicture(String $username, String $picture): void;

    public function getPicture(String $username): String;

    public function addPhone(String $username, String $phone): void;

    public function updatePassword(String $username, String $pass): void;

    public function saveGame(String $username, String $id):void;

    public function getGames(String $username): String;

    public function getToken(String $user): int;

    public function getUsernameToken(int $token): String;


}