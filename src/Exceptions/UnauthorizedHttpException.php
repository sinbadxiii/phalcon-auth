<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Exceptions;

/**
 * Class UnauthorizedHttpException
 * @package Sinbadxiii\PhalconAuth\Exceptions
 */
class UnauthorizedHttpException extends Exception
{
    protected $message = 'Unauthorized';
}