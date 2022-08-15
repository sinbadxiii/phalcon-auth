<?php

namespace Sinbadxiii\PhalconAuth\Guard;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface GuardInterface
{
    public function check();
    public function user();
    public function setUser(AuthenticatableInterface $user);
    public function id();
    public function guest();
    public function validate(array $credentials = []);
}