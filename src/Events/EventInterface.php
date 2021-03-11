<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Events;

interface EventInterface
{
    public function getType(): string;
}