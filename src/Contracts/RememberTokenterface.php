<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

interface RememberTokenterface
{
    public function getToken(): string;
    public function getUserAgent(): string;
}