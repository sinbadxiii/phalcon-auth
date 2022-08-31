<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use InvalidArgumentException;
use Sinbadxiii\PhalconAuth\Access\AccessInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardInterface;

/**
 * Class Manager
 * @package Sinbadxiii\PhalconAuth
 */
class Manager implements ManagerInterface
{
    /**
     * @var array
     */
    protected array $guards = [];

    /**
     * @var AccessInterface|null
     */
    protected ?AccessInterface $activeAccess = null;

    /**
     * @var array
     */
    protected array $accessList = [];

    /**
     * @var GuardInterface
     */
    protected $defaultGuard;

    /**
     * @var GuardInterface
     */
    protected $guard;

    /**
     * @param string|null $name
     * @return GuardInterface
     * @throws \Exception
     */
    public function guard(?string $name = null): GuardInterface
    {
        if ($name === null) {
            return $this->defaultGuard;
        }

        if (!isset($this->guards[$name])) {
            throw new \Exception("Guard [{$name}] is not defined.");
        }

        return $this->guards[$name];
    }

    /**
     * @return GuardInterface
     */
    public function getDefaultGuard(): GuardInterface
    {
        return $this->defaultGuard;
    }

    /**
     * @param GuardInterface $guard
     * @return $this
     */
    public function setDefaultGuard(GuardInterface $guard): static
    {
        $this->defaultGuard = $guard;

        return $this;
    }

    /**
     * @param string $nameGuard
     * @param GuardInterface $guard
     * @param bool $isDefault
     * @return $this
     */
    public function addGuard(string $nameGuard, GuardInterface $guard, bool $isDefault = false): static
    {
        $this->guards[$nameGuard] = $guard;

        if ($isDefault === true) {
            $this->defaultGuard = $guard;
        }

        return $this;
    }

    /**
     * @return null|AccessInterface
     */
    public function getAccess(): ?AccessInterface
    {
        return $this->activeAccess;
    }

    /**
     * @param AccessInterface $access
     * @return $this
     */
    public function setAccess(AccessInterface $access): static
    {
        $this->activeAccess = $access;

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

    /**
     * @param string $accessName
     * @return ManagerInterface|null
     */
    public function access(string $accessName): ?ManagerInterface
    {
        if (!isset($this->accessList[$accessName]) || !class_exists($this->accessList[$accessName])) {
            throw new InvalidArgumentException(
                "Access with '" . $accessName . "' name is not included in the access list"
            );
        }

        $this->activeAccess = new $this->accessList[$accessName];

        return $this;
    }

    /**
     * @param ...$actions
     * @return $this
     */
    public function except(...$actions): static
    {
        $this->activeAccess->setExceptActions(...$actions);

        return $this;
    }

    /**
     * @param ...$actions
     * @return $this
     */
    public function only(...$actions): static
    {
        $this->activeAccess->setOnlyActions(...$actions);

        return $this;
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