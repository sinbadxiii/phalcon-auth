<?php

namespace Sinbadxiii\PhalconAuth;

interface RememberTokenInterface
{
    public function getToken(): string;
    public function getUserAgent(): string;
}