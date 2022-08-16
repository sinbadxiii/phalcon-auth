<?php

namespace Sinbadxiii\PhalconAuth\Guard;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface GuardStatefulInterface
{
    public function attempt(array $credentials = [], $remember = false): bool;
    public function login(AuthenticatableInterface $user, $remember = false): void;
    public function loginById($id, $remember = false);
    public function once(array $credentials = []);
    public function viaRemember();
    public function logout();
}