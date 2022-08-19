<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use Closure;
use InvalidArgumentException;
use Sinbadxiii\PhalconAuth\Access\AccessInterface;
use Phalcon\Di\Di;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Phalcon\Config\ConfigInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardInterface;

use function is_null;
use function call_user_func;

/**
 * Class Manager
 * @package Sinbadxiii\PhalconAuth
 */
class Manager implements ManagerInterface
{
    /**
     * @var mixed
     */
    protected ConfigInterface $config;

    /**
     * @var mixed
     */
    protected $security;

    /**
     * @var array
     */
    protected array $customGuards = [];

    /**
     * @var array
     */
    protected array $customAdapters = [];

    /**
     * @var array
     */
    protected array $guards = [];

    /**
     * @var AccessInterface|null
     */
    protected ?AccessInterface $access = null;

    /**
     * @var array
     */
    protected array $accessList = [];

    public function __construct(ConfigInterface $config = null, $security = null)
    {
        $this->config = $config ?? Di::getDefault()->getShared("config")->auth;

        if ($this->config === null) {
            throw new InvalidArgumentException(
                "Configuration file auth.php (or key config->auth into your config) does not exist"
            );
        }

        $this->security = $security ?? Di::getDefault()->getShared("security");
    }

    /**
     * @param string $name
     * @return \Phalcon\Config\ConfigInterface|null
     */
    protected function getConfigGuard(string $name): ?ConfigInterface
    {
        return $this->config->guards->{$name};
    }

    /**
     * @param string|null $name
     * @return \Sinbadxiii\PhalconAuth\Guard\GuardInterface
     */
    public function guard(?string $name = null): GuardInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function resolve(string $name)
    {
        $configGuard = $this->getConfigGuard($name);

        if (is_null($configGuard)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }

        $providerAdapter = $this->getAdapterProvider($configGuard->provider);

        if (isset($this->customGuards[$configGuard->driver])) {
            return call_user_func(
                $this->customGuards[$configGuard->driver],
                $name,
                $providerAdapter,
                $configGuard
            );
        }

        $guardName = sprintf("\\%s\\Guard\\%s",
            __NAMESPACE__,
            ucfirst($configGuard->driver)
        );

        if (!class_exists($guardName)) {
            throw new InvalidArgumentException(
                "Auth driver [{$configGuard->driver}] for guard [{$name}] is not defined."
            );
        }

        return new $guardName($name, $providerAdapter, $configGuard);
    }

    /**
     * @param string|null $provider
     * @return mixed|\Sinbadxiii\PhalconAuth\Adapter\AdapterInterface|void
     */
    public function getAdapterProvider(string $provider = null)
    {
        $configProvider = $this->config->providers->{$provider};

        if ($configProvider === null) {
            return;
        }

        $adapterName = $configProvider->adapter;

        if ($adapterName === null) {
            throw new InvalidArgumentException(
                "Adapter not assigned in config->auth->providers->".$provider."->adapter = ?"
            );
        }

        if (isset($this->customAdapters[$adapterName])) {
            return call_user_func(
                $this->customAdapters[$adapterName],
                $this->security,
                $configProvider
            );
        }

        $adapterClass = sprintf("\\Sinbadxiii\\PhalconAuth\\Adapter\\%s",
            ucfirst($adapterName)
        );

        if (!class_exists($adapterClass)) {
            throw new \InvalidArgumentException($adapterClass . " not found");
        }

        $adapter = new $adapterClass(
            $this->security,
            $configProvider
        );

        if (!($adapter instanceof AdapterInterface)) {
            throw new \InvalidArgumentException($adapterClass . " not implementing AdapterInterface");
        }

        return $adapter;
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->defaults->guard;
    }

    /**
     * @param $driver
     * @param Closure $callback
     * @return $this
     */
    public function addGuard($driver, Closure $callback): ManagerInterface
    {
        $this->customGuards[$driver] = $callback;

        return $this;
    }

    /**
     * @param $name
     * @param Closure $callback
     * @return $this
     */
    public function addProviderAdapter($name, Closure $callback): static
    {
        $this->customAdapters[$name] = $callback;

        return $this;
    }

    /**
     * @return null|AccessInterface
     */
    public function getAccess(): ?AccessInterface
    {
        return $this->access;
    }

    /**
     * @param AccessInterface $access
     * @return static
     */
    public function setAccess(AccessInterface $access): static
    {
        $this->access = $access;

        return $this;
    }

    /**
     * @param array $accessList
     * @return $this
     */
    public function setAccessList(array $accessList): static
    {
        $this->accessList = $accessList;

        return $this;
    }

    /**
     * @param array $accessList
     * @return $this
     */
    public function addAccessList(array $accessList): static
    {
        $this->accessList += $accessList;

        return $this;
    }

    public function access(string $accessName): ?AccessInterface
    {
        if (!isset($this->accessList[$accessName]) || !class_exists($this->accessList[$accessName])) {
            throw new InvalidArgumentException(
                "Access with '" . $accessName . "' name is not included in the access list"
            );
        }

        $this->access = new $this->accessList[$accessName];

        return $this->access;
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