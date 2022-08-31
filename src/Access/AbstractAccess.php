<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

use Phalcon\Di\Injectable;

use function in_array;

/**
 * Class AbstractAccess
 * @package Sinbadxiii\PhalconAuth\Access
 */
abstract class AbstractAccess extends Injectable implements AccessInterface
{
    /**
     * @var array
     */
    protected array $exceptActions = [];

    /**
     * @var array
     */
    protected array $onlyActions   = [];

    /**
     * @return bool
     */
    abstract public function allowedIf(): bool;

    /**
     * @param ...$actions
     * @return void
     */
    public function setExceptActions(...$actions): void
    {
        $this->exceptActions = $actions;
    }

    /**
     * @return array
     */
    public function getExceptActions(): array
    {
        return $this->exceptActions;
    }

    /**
     * @param ...$actions
     * @return void
     */
    public function setOnlyActions(...$actions): void
    {
        $this->onlyActions = $actions;
    }

    /**
     * @return array
     */
    public function getOnlyActions(): array
    {
        return $this->onlyActions;
    }

    public function redirectTo()
    {
    }

    /**
     * @param string $actionName
     * @return bool
     */
    public function isAllowed(string $actionName): bool
    {
        $isAllowed = $this->allowedIf();

        if (!empty($this->exceptActions)) {
            if ($isAllowed || in_array($actionName, $this->exceptActions)) {
                return true;
            }
        }

        if (!empty($this->onlyActions)) {
            if ($isAllowed && in_array($actionName, $this->onlyActions)) {
                return true;
            }
        }

        if (empty($this->onlyActions) && empty($this->exceptActions)) {
            if ($isAllowed) {
                return true;
            }
        }

        return false;
    }
}