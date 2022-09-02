<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

interface AdapterWithRememberTokenInterface
{
    public function findFirstByToken($identifier, $token, $user_agent): ?AuthenticatableInterface;
    public function createRememberToken(RememberingInterface $user): RememberTokenInterface;
}