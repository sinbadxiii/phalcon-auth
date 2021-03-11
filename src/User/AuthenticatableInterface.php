<?php

namespace Sinbadxiii\PhalconAuth\User;

interface AuthenticatableInterface
{
    public function getAuthIdentifier();
    public function getAuthPassword();
}