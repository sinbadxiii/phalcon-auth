<?php

namespace Sinbadxiii\PhalconAuth\Guard;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

use function is_null;

/**
 * Trait GuardHelper
 * @package Sinbadxiii\PhalconAuth\Guard
 */
trait GuardHelper
{
    /**
     * @var
     */
    protected $user;

    /**
     * @return mixed
     */
    public function id(): mixed
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    /**
     * @param \Sinbadxiii\PhalconAuth\AuthenticatableInterface $user
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
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * @return bool
     */
    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    /**
     * @return mixed
     */
    public function getUser(): mixed
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * @return AuthenticatableInterface|void
     */
    public function authenticate()
    {
        if (!is_null($user = $this->user())) {
            return $user;
        }
    }
}