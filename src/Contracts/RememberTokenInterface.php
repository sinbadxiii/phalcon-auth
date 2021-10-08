<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface RememberTokenInterface
{
    public function getToken(): string;
    public function getUserAgent(): string;
}