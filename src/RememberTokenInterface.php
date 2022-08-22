<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

interface RememberTokenInterface
{
    public function getToken(): string;
    public function getUserAgent(): string;
}