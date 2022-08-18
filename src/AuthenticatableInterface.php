<?php

namespace Sinbadxiii\PhalconAuth;

interface AuthenticatableInterface
{
    public function getAuthIdentifier(): mixed;
    public function getAuthPassword(): string;
}