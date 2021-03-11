<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AuthProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $providerName = 'auth';

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $di->setShared($this->providerName, function () {
            return new Auth();
        });
    }
}