<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Events;

use ReflectionClass;

abstract class EventAbstract implements EventInterface
{
    public function getType(): string
    {
        $reflect = new ReflectionClass($this);
        return lcfirst($reflect->getShortName());
    }
}