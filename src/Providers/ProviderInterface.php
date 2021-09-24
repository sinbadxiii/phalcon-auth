<?php

namespace Sinbadxiii\PhalconAuth\Providers;

use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;

interface ProviderInterface
{
    public function retrieveByCredentials(array $credentials);
    public function retrieveById($id);
    public function validateCredentials(AuthenticatableInterface $user, array $credentials);
}