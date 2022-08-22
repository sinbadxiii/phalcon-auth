<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

interface BasicAuthInterface
{
    public function basic(string $field = 'email', array $extraConditions = []): bool;
    public function onceBasic(string $field = 'email', array $extraConditions = []);
}