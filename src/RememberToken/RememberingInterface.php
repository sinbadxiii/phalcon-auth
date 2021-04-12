<?php

namespace Sinbadxiii\PhalconAuth\RememberToken;

interface RememberingInterface
{
    public function getRememberToken(string $token);
    public function setRememberToken($value);
}