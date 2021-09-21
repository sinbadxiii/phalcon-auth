<?php

namespace Sinbadxiii\PhalconAuth\Contracts;

use Sinbadxiii\PhalconAuth\User\AuthenticatableInterface;

interface BasicAuth
{
    public function basic($field = 'email', $extraConditions = []);
    public function onceBasic($field = 'email', $extraConditions = []);
}