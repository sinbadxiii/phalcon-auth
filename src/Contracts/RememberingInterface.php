<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface RememberingInterface
{
    public function getRememberToken();
    public function setRememberToken(RememberTokenInterface $value);
}