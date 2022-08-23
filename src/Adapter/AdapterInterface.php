<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface AdapterInterface
{
    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface;
    public function retrieveById($id): ?AuthenticatableInterface;
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool;
}