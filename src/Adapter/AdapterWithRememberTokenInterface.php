<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

interface AdapterWithRememberTokenInterface
{
    public function retrieveByToken($identifier, $token, $user_agent);
    public function createRememberToken(RememberingInterface $user): RememberTokenInterface;
}