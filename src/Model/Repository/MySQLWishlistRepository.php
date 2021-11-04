<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model\Repository;

use PDO;
use SallePW\SlimApp\Model\WishlistRepository;

final class MySQLWishlistRepository implements WishlistRepository {

    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    public function saveWishedGame (String $username, String $id): void{
        $query = <<<'QUERY'
        INSERT INTO Wishlist(game_id, username) VALUES(:game_id, :username)
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':game_id', $id, PDO::PARAM_STR);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();
    }

    public function getWishedGames (String $username){
        $query = <<<'QUERY'
        SELECT * FROM Wishlist WHERE username LIKE :username
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();
        $events = $statement->fetchAll();

        return $events;
    }

    public function removeWishedGame(String $username, int $game_id): void {
        $query = <<<'QUERY'
        DELETE FROM Wishlist WHERE username LIKE :username AND game_id LIKE :game_id
        QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':game_id', $game_id, PDO::PARAM_STR);

        $statement->execute();
    }
}