<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

interface WishlistRepository {
    public function saveWishedGame (String $username, String $id): void;

    public function getWishedGames (String $username);

    public function removeWishedGame(String $username, int $game_id): void;
}