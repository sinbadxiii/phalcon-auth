<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

interface RememberingInterface
{
    public function getRememberToken(): ?RememberTokenInterface;
    public function createRememberToken(): RememberTokenInterface;

}