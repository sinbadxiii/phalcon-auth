<?php

namespace Sinbadxiii\PhalconAuth\RememberToken;

interface RememberingInterface
{
    public function getRememberToken();
    public function setRememberToken($value);
}