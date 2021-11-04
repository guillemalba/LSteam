<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

interface FriendRepository {
    public function save(int $friend1, int $friend12, int $accepted): void;

    public function getAllFriends(int $myUser);

    public function getAllFriendsRequests(int $myUser);

    public function acceptedRequest(int $friend1, int $friend2): void;

    public function removeRequest(int $friend1, int $friend2): void;
}