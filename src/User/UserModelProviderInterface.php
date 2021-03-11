<?php

namespace Sinbadxiii\PhalconAuth\User;

interface UserModelProviderInterface
{
    public function retrieveByCredentials(array $credentials);
    public function retrieveById($id);
    public function validateCredentials(AuthenticatableInterface $user, array $credentials);
}