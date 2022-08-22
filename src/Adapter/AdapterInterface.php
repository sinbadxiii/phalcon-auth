<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface AdapterInterface
{
    public function retrieveByCredentials(array $credentials);
    public function retrieveById($id);
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool;
}