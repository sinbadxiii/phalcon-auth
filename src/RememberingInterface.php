<?php

namespace Sinbadxiii\PhalconAuth;

interface RememberingInterface
{
    public function getRememberToken(): ?RememberTokenInterface;
    public function setRememberToken(RememberTokenInterface $value);
    public function createRememberToken(): RememberTokenInterface;

}