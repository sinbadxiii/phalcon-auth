<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use Closure;
use InvalidArgumentException;
use Phalcon\Di;

/**
 * Class Auth
 * @package Sinbadxiii\PhalconAuth
 */
class Auth
{
    /**
     * @var mixed
     */
    protected $config;

    /**
     * @var mixed
     */
    protected $security;

    /**
     * @var array
     */
    protected $customGuards = [];

    /**
     * @var array
     */
    protected $guards = [];

    public function __construct($config = null, $security = null)
    {
        $this->config = $config ?? Di::getDefault()->getShared("config")->auth;
        $this->security = $security ?? Di::getDefault()->getShared("security");
    }

    /**
     * @param $name
     * @return array
     */
    protected function getConfigGuard(string $name)
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

        if (isset($this->customGuards[$configGuard['driver']])) {
            return $this->callCustomGuard($name, $configGuard);
        }

        $className = sprintf("\\%s\\Guards\\%sGuard",
            __NAMESPACE__,
            ucfirst($configGuard->driver));


        $provider = $this->createProvider($configGuard ?? null);
        $guard = new $className($name, $provider);

        if (class_exists($className)) {
            return $guard;
        }

        throw new InvalidArgumentException(
            "Auth driver [{$configGuard->driver}] for guard [{$name}] is not defined."
        );
    }

    public function createProvider($configGuard = null)
    {
        $driver = sprintf("\\%s\\Providers\\%sProvider",
            __NAMESPACE__,
            ucfirst($this->config->providers->{$configGuard->provider}->driver)
        );

        return new $driver(
            $this->security,
            $this->config->providers->{$configGuard->provider}
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultDriver()
    {
        return $this->config->defaults->guard;
    }

    public function extend($driver, Closure $callback)
    {
        $this->customGuards[$driver] = $callback;

        return $this;
    }

    protected function callCustomGuard($name, $config)
    {
        return $this->customGuards[$config['driver']]($name, $config);
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