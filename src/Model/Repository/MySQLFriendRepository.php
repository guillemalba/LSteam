<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model\Repository;

use PDO;
use SallePW\SlimApp\Model\FriendRepository;

final class MySQLFriendRepository implements FriendRepository {

    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    //$friend1 qui envia la solicitud, $friend12 a qui l'envia
    public function save(int $friend1, int $friend2, int $accepted):void {

        $query = <<<'QUERY'
        INSERT INTO Friends(friend1, friend2, accepted) VALUES(:friend1, :friend2, :accepted)
        QUERY;
        $statement = $this->database->connection()->prepare($query);


        $statement->bindParam(':friend1', $friend1, PDO::PARAM_STR);
        $statement->bindParam(':friend2', $friend2, PDO::PARAM_STR);
        $statement->bindParam(':accepted', $accepted, PDO::PARAM_STR);

        $statement->execute();
    }

    public function getAllFriends(int $myUser){
        $query = <<<'QUERY'
        SELECT * FROM Friends WHERE (friend1 LIKE :friend OR friend2 LIKE :friend) AND accepted LIKE 1
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':friend', $myUser, PDO::PARAM_STR);
        $statement->execute();
        $events = $statement->fetchAll();

        return $events;
    }

    public function getAllFriendsRequests(int $myUser){
        $query = <<<'QUERY'
        SELECT * FROM Friends WHERE friend2 LIKE :friend AND accepted LIKE 0
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':friend', $myUser, PDO::PARAM_STR);
        $statement->execute();
        $events = $statement->fetchAll();

        return $events;
    }

    public function acceptedRequest(int $friend1, int $friend2): void {

        $currentDate = date("Y-m-d H:i:s");

        $query = <<<'QUERY'
        UPDATE Friends SET accept_date = :accept_date, accepted = 1 WHERE friend1 LIKE :friend1 AND friend2 LIKE :friend2
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':friend1', $friend1, PDO::PARAM_STR);
        $statement->bindParam(':friend2', $friend2, PDO::PARAM_STR);
        $statement->bindParam(':accept_date', $currentDate, PDO::PARAM_STR);

        $statement->execute();
    }

    public function checkSend(int $username, int $friend): int{
        $query = <<<'QUERY'
        SELECT COUNT(*) FROM Friends WHERE friend1 LIKE :username AND friend2 LIKE :friend
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':friend', $friend, PDO::PARAM_STR);

        $statement->execute();

        $response = $statement->fetchColumn();
        echo $statement->fetchColumn();
        return $response = intval($response);
    }

    public function removeRequest(int $friend1, int $friend2): void {
        $query = <<<'QUERY'
        DELETE FROM Friends WHERE friend1 LIKE :friend1 AND friend2 LIKE :friend2
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':friend1', $friend1, PDO::PARAM_STR);
        $statement->bindParam(':friend2', $friend2, PDO::PARAM_STR);

        $statement->execute();
    }
}