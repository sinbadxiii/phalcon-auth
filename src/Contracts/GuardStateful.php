<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

use Sinbadxiii\PhalconAuth\User\AuthenticatableInterface;

interface GuardStateful
{
    public function attempt(array $credentials = [], $remember = false);
    public function login(AuthenticatableInterface $user, $remember = false);
    public function loginById($id, $remember = false);
    public function once(array $credentials = []);
    public function viaRemember();
    public function logout();
}