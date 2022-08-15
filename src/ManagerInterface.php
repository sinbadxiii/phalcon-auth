<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

interface ManagerInterface
{
    public function guard($name = null);
    public function access(string $accessName);
}