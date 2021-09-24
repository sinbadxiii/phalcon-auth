<?php

namespace Sinbadxiii\PhalconAuth\Providers\Users;

use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;

interface UsersProviderInterface
{
    public function retrieveByCredentials(array $credentials);
    public function retrieveById($id);
    public function validateCredentials(AuthenticatableInterface $user, array $credentials);
}