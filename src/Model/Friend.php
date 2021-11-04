<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;
use DateTime;

final class Friend{

    private string $username;
    private DateTime $became_friends;

    public function __construct(string $username, DateTime $became_friends) {
        $this->username = $username;
        $this->became_friends = $became_friends;
    }

    public function username(): string {
        return $this->username;
    }

    public function setUsername(string $username): self{
        $this->username = $username;
        return $this;
    }

    public function became_friends(): DateTime {
        return $this->became_friends;
    }

    public function setBecame_friends(DateTime $became_friends) {
        $this->became_friends = $became_friends;
        return $this;
    }
}