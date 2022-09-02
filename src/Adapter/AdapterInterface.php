<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface AdapterInterface
{
    public function findFirstByCredentials(array $credentials): ?AuthenticatableInterface;
    public function findFirstById($id): ?AuthenticatableInterface;
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool;
}