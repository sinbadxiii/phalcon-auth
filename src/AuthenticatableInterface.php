<?php

namespace Sinbadxiii\PhalconAuth;

interface AuthenticatableInterface
{
    public function getAuthIdentifier();
    public function getAuthPassword();
}