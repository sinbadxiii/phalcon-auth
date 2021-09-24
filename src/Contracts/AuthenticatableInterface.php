<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface AuthenticatableInterface
{
    public function getAuthIdentifier();
    public function getAuthPassword();
}