<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model\Repository;

use PDO;
use PhpParser\Node\Scalar\String_;
use Ramsey\Uuid\Uuid;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class MySQLUserRepository implements UserRepository {

    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    public function save(User $user):void {

        $query = <<<'QUERY'
        INSERT INTO User(username, email, password, birthday, phone, wallet, token, activate, profile_picture)
        VALUES(:username, :email, :password, :birthday, :phone, :wallet, :token, :activate, :profile_picture)
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $username = $user->username();
        $email = $user->email();
        $password = $user->password();
        $birthday = $user->birthday()->format('Y-m-d');
        $phone = $user->phone();
        $wallet = $user->wallet();
        $token = $user->token();
        $activate = $user->activate();
        $profile_picture = $user->profile_picture();

        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->bindParam(':password', $password, PDO::PARAM_STR);
        $statement->bindParam(':birthday', $birthday, PDO::PARAM_STR);
        $statement->bindParam(':phone', $phone, PDO::PARAM_STR);
        $statement->bindParam(':wallet', $wallet, PDO::PARAM_STR);
        $statement->bindParam(':token', $token, PDO::PARAM_STR);
        $statement->bindParam(':activate', $activate, PDO::PARAM_STR);
        $statement->bindParam(':profile_picture', $profile_picture, PDO::PARAM_STR);

        $statement->execute();
        $success = true;
    }

    public function checkUserRegister(String $username):int {
        $query = <<<'QUERY'
        SELECT COUNT(id) FROM User WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam(':username', $username, PDO::PARAM_STR);

        $statement->execute();

        $response = $statement->fetchColumn();
        
        return $response = intval($response);
    }

    public function checkEmailRegister(String $email):int {
        $query = <<<'QUERY'
        SELECT COUNT(id) FROM User WHERE email LIKE :email
        QUERY;

        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam(':email', $email, PDO::PARAM_STR);

        $statement->execute();

        $response = $statement->fetchColumn();

        return $response = intval($response);
    }

    public function checkUser(String $user):int {

        if (strpos($user, '@') !== false) {
            $query = <<<'QUERY'
            SELECT COUNT(id) FROM User WHERE email LIKE :email
        QUERY;
            
            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam(':email', $user, PDO::PARAM_STR);

        } else {
            $query = <<<'QUERY'
            SELECT COUNT(id) FROM User WHERE username LIKE :username
        QUERY;

            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam(':username', $user, PDO::PARAM_STR);
        }

        $statement->execute();

        $response = $statement->fetchColumn();
        
        return $response = intval($response);
    }

    public function checkPass(String $pass, String $user):int {
        $query = <<<'QUERY'
        SELECT password AS pwd FROM User WHERE username LIKE :user OR email LIKE :user
        QUERY;
        
        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $user, PDO::PARAM_STR);
        $statement->execute();
        
        $pwd = $statement->fetchColumn();

        return intval(password_verify($pass, $pwd));
    }

    public function checkToken(int $token): bool{
        $query = <<<'QUERY'
        SELECT COUNT(token) AS num_token FROM User WHERE token LIKE :token
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':token', $token, PDO::PARAM_STR);
        $statement->execute();

        $count = $statement->fetchColumn();

        if ($count >= 1) {
            return false;
        } else {
            return true;
        }
    }

    public function getUsername(String $user): String{
        $query = <<<'QUERY'
        SELECT username FROM User WHERE email LIKE :user OR username LIKE :user
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $user, PDO::PARAM_STR);
        $statement->execute();

        $username = $statement->fetchColumn();

        return $username;
    }

    public function getUsername2(int $id): String{
        $query = <<<'QUERY'
        SELECT username FROM User WHERE id LIKE :id
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();

        $username = $statement->fetchColumn();

        return $username;
    }

    public function getEmail(int $token): String{
        $query = <<<'QUERY'
        SELECT email FROM User WHERE token LIKE :token
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':token', $token, PDO::PARAM_STR);
        $statement->execute();

        $email = $statement->fetchColumn();

        return $email;
    }

    public function getId(String $user): int{
        $query = <<<'QUERY'
        SELECT id FROM User WHERE username LIKE :user
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $user, PDO::PARAM_STR);
        $statement->execute();

        $id = $statement->fetchColumn();

        return intval($id);
    }

    public function getEmail2(String $user): String{
        $query = <<<'QUERY'
        SELECT email FROM User WHERE username LIKE :user
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $user, PDO::PARAM_STR);
        $statement->execute();

        $email = $statement->fetchColumn();

        return $email;
    }

    public function getBirthday(String $user): String{
        $query = <<<'QUERY'
        SELECT birthday FROM User WHERE username LIKE :user
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $user, PDO::PARAM_STR);
        $statement->execute();

        $birthday = $statement->fetchColumn();

        return $birthday;
    }

    public function getPhone(String $user): String{
        $query = <<<'QUERY'
        SELECT phone FROM User WHERE username LIKE :user
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $user, PDO::PARAM_STR);
        $statement->execute();

        $phone = $statement->fetchColumn();

        return $phone;
    }


    public function activateUser(String $username): void{
        $query = <<<'QUERY'
        UPDATE User SET activate = 1 WHERE username LIKE :user
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $username, PDO::PARAM_STR);
        $statement->execute();
    }

    public function checkActivation(int $token):int {
        $query = <<<'QUERY'
        SELECT activate FROM User WHERE token LIKE :token
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':token', $token, PDO::PARAM_STR);
        $statement->execute();

        $activate = intval($statement->fetchColumn());
        return $activate;
    }

    public function checkActivation2(String $username):int {
        $query = <<<'QUERY'
        SELECT activate FROM User WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $activate = intval($statement->fetchColumn());
        return $activate;
    }

    public function checkMoney(String $username): float{
        $query = <<<'QUERY'
        SELECT wallet FROM User WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $money = floatval($statement->fetchColumn());
        return $money;
    }

    public function addMoney(String $username, float $amount): void{
        $money = $this->checkMoney($username);
        $newMoney = $money + $amount;

        $query = <<<'QUERY'
        UPDATE User SET wallet = :money WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':money', $newMoney, PDO::PARAM_STR);
        $statement->execute();
    }

    public function addPicture(String $username, String $picture): void{
        $query = <<<'QUERY'
        UPDATE User SET profile_picture = :picture WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':picture', $picture, PDO::PARAM_STR);
        $statement->execute();
    }

    public function getPicture(String $username): String{
        $query = <<<'QUERY'
        SELECT profile_picture FROM User WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $profile_picture = $statement->fetchColumn();

        return $profile_picture;
    }

    public function addPhone(String $username, String $phone): void{
        $query = <<<'QUERY'
        UPDATE User SET phone = :phone WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':phone', $phone, PDO::PARAM_STR);
        $statement->execute();
    }

    public function updatePassword(String $username, String $pass): void{
        $query = <<<'QUERY'
        UPDATE User SET password = :pass WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':pass', $pass, PDO::PARAM_STR);
        $statement->execute();
    }

    public function saveGame(String $username, String $id):void {

        $id .= '-';
        $query = <<<'QUERY'
        UPDATE User SET my_games = :id WHERE username LIKE :username
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();
        $success = true;
    }

    public function getGames(String $username): String{
        $query = <<<'QUERY'
        SELECT my_games FROM User WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $my_games = $statement->fetchColumn();

        if ($my_games == NULL) {
            $my_games = 'null';
        }

        return $my_games;
    }

    public function getToken(String $user): int {

        $query = <<<'QUERY'
        SELECT token FROM User WHERE username LIKE :user
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':user', $user, PDO::PARAM_STR);
        $statement->execute();

        $token = $statement->fetchColumn();

        return intval($token);

    }

    public function getUsernameToken(int $token): String{
        $query = <<<'QUERY'
        SELECT username FROM User WHERE token LIKE :token
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':token', $token, PDO::PARAM_STR);
        $statement->execute();

        $username = $statement->fetchColumn();

        return $username;
    }
    
}