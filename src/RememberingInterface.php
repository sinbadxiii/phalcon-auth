<?php

namespace Sinbadxiii\PhalconAuth;

interface RememberingInterface
{
    public function getRememberToken(): ?RememberTokenInterface;
    public function createRememberToken(): RememberTokenInterface;

}