<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

interface AuthenticatableInterface
{
    public function getAuthIdentifier();
    public function getAuthPassword(): string;
}