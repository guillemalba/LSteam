<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;
use PhpParser\Node\Scalar\String_;
use Ramsey\Uuid\Uuid;
use DateTime;

final class User{
    
    private string $username;
    private string $email;
    private string $password;
    private DateTime $birthday;
    private string $phone;
    private int $token;
    private float $wallet;
    private int $activate;
    private String $profile_picture;

    public function __construct(string $username, string $email, string $password, DateTime $birthday, string $phone, int $token, float $wallet, int $activate, String $profile_picture) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->birthday = $birthday;
        if ($phone != '') {
            $this->phone = $phone;
        }
        $this->token = $token;
        $this->wallet = $wallet;
        $this->activate = $activate;
        $this->profile_picture = $profile_picture;
    }

    public function username(): string {
        return $this->username;
    }

    public function setUsername(string $username): self{
        $this->username = $username;
        return $this;
    }

    public function email(): string {
        return $this->email;
    }

    public function setEmail(string $email): self{
        $this->email = $email;
        return $this;
    }

    public function password(): string {
        return $this->password;
    }

    public function setPassword(string $password): self{
        $this->password = $password;
        return $this;
    }

    public function birthday(): DateTime {
        return $this->birthday;
    }

    public function setBirthday(DateTime $birthday) {
        $this->birthday = $birthday;
        return $this;
    }

    public function phone(): string {
        return $this->phone;
    }

    public function setPhone(string $phone) {
        $this->phone = $phone;
        return $this;
    }

    public function token(): int {
        return $this->token;
    }

    public function setToken(int $token) {
        $this->token = $token;
        return $this;
    }

    public function wallet(): float {
        return $this->wallet;
    }

    public function setWallet(float $wallet) {
        $this->wallet = $wallet;
        return $this;
    }

    public function activate(): int {
        return $this->activate;
    }

    public function setActivate(int $activate) {
        $this->activate = $activate;
        return $this;
    }

    public function profile_picture(): String
    {
        return $this->profile_picture;
    }

    public function setProfile_picture(String $profile_picture): void
    {
        $this->profile_picture = $profile_picture;
    }


}