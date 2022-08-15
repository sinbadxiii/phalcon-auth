<?php

namespace Sinbadxiii\PhalconAuth\Tests\Users;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

/**
 * Class UserStub
 * @package Sinbadxiii\PhalconAuth\Tests\Users
 */
class UserModelStub implements AuthenticatableInterface, RememberingInterface
{
    public int $id;
    public int $password;
    public RememberingInterface $remember_token;

    public function getAuthIdentifier()
    {
        $this->id;
    }

    public function getAuthPassword()
    {
        $this->password;
    }

    public function setRememberToken(RememberTokenInterface $value)
    {
        $this->remember_token = $value;

        return $this;
    }

    public function createRememberToken(): ?RememberTokenInterface
    {
    }

    public function getRememberToken(): ?RememberTokenInterface
    {
    }
}