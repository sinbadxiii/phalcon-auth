<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface RememberingInterface
{
    public function getRememberToken(): ?RememberTokenInterface;
    public function setRememberToken(RememberTokenInterface $value);
}