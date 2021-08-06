<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use InvalidArgumentException;
use Phalcon\Di;

/**
 * Class Auth
 * @package Sinbadxiii\PhalconAuth
 */
class Auth
{
    protected $config;
    protected $guards = [];

    public function __construct($config = null)
    {
        if (is_null($config)) {
            $config = Di::getDefault()->getShared("config")->auth;
        }
        $this->config = $config;
    }

    /**
     * @param $name
     * @return array
     */
    protected function getConfigGuard(string $name): object
    {
        return $this->config->guards->{$name};
    }

    /**
     * @param null $name
     * @return mixed
     */
    public function guard($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function resolve($name)
    {
        $configGuard = $this->getConfigGuard($name);

        if (is_null($configGuard)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }

        $className = sprintf("\\%s\\Guards\\%sGuard",
            __NAMESPACE__,
            ucfirst($configGuard->driver));

        $provider = $this->createUserProvider($configGuard ?? null);

        $guard = new $className($name, $provider);

        if (class_exists($className)) {
            return $guard;
        }

        throw new InvalidArgumentException(
            "Auth driver [{$configGuard->driver}] for guard [{$name}] is not defined."
        );
    }

    public function createUserProvider($configGuard = null)
    {
        $driver = sprintf("\\%s\\User\\User%sProvider",
            __NAMESPACE__,
            ucfirst($this->config->providers->{$configGuard->provider}->driver)
        );

        return new $driver(
            Di::getDefault()->getShared("security"),
            $this->config->providers->{$configGuard->provider}->model
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultDriver()
    {
        return $this->config->defaults->guard;
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return $this->guard()->{$method}(...$params);
    }
}