<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use Sinbadxiii\PhalconAuth\Access\AccessInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardInterface;

interface ManagerInterface
{
    public function guard(?string $name = null): GuardInterface;
    public function access(string $accessName): ?AccessInterface;
}