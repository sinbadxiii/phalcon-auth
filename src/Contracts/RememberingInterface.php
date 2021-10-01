<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface RememberingInterface
{
    public function getRememberToken(): RememberTokenterface;
    public function setRememberToken(RememberTokenterface $value);
}