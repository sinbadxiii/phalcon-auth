<?php

namespace Sinbadxiii\PhalconAuth\Guards;

use Sinbadxiii\PhalconAuth\User\AuthenticatableInterface;

/**
 * Trait UserHelper
 * @package Sinbadxiii\PhalconAuth\Guard
 */
trait UserHelper
{
    protected $user;

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->user()->getAuthIdentifier();
    }

    public function setUser(AuthenticatableInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function hasUser()
    {
        return !is_null($this->user);
    }

    public function getUser()
    {
        return $this->user;
    }
}