<?php

namespace Sinbadxiii\PhalconAuth\Tests\User;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

/**
 * Class UserModelFake
 * @package Sinbadxiii\PhalconAuth\Tests\User
 */
class UserModelFake implements AuthenticatableInterface, RememberingInterface
{
    public mixed $id;
    public string $password;
    public RememberTokenInterface $remember_token;

    public function __construct($data)
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function setRememberToken(RememberTokenInterface $value): static
    {
        $this->remember_token = $value;

        return $this;
    }

    public function createRememberToken(): RememberTokenInterface
    {
        return new RememberTokenModelFake();
    }

    public function getRememberToken(string $token = null): ?RememberTokenInterface
    {
        return $this->remember_token;
    }

    public function getId()
    {
        return $this->id;
    }
}