<?php

namespace Sinbadxiii\PhalconAuth\Guards;

use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;

/**
 * Trait GuardHelper
 * @package Sinbadxiii\PhalconAuth\Guard
 */
trait GuardHelper
{
    protected $user;

    /**
     * @return mixed
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    /**
     * @param AuthenticatableInterface $user
     * @return $this
     */
    public function setUser(AuthenticatableInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * @return bool
     */
    public function hasUser()
    {
        return !is_null($this->user);
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    public function authenticate()
    {
        if (!is_null($user = $this->user())) {
            return $user;
        }
    }
}