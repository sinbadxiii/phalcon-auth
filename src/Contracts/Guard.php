<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface Guard
{
    public function check();
    public function user();
    public function setUser(AuthenticatableInterface $user);
    public function id();
    public function guest();
    public function validate(array $credentials = []);
}