<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Tests\Access;

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;

/**
 * Class AuthStub
 * @package Sinbadxiii\PhalconAuth\Tests\Access
 */
class AuthStub extends AbstractAccess
{
    /**
     * @var bool
     */
    public bool $access;

    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        return $this->access;
    }
}