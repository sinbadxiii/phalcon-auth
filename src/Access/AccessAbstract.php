<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

use Phalcon\Di\Injectable;

use function in_array;

abstract class AccessAbstract extends Injectable implements AccessInterface
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
    public function except(...$actions): void
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
    public function only(...$actions): void
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
     * @return bool
     */
    public function isAllowed(): bool
    {
        $exceptActions = $this->getExceptActions();
        $onlyActions   = $this->getOnlyActions();

        $action = $this->dispatcher->getActionName();

        $isAllowed     = $this->allowedIf();

        if (!empty($exceptActions)) {
            if ($isAllowed || in_array($action, $exceptActions)) {
                return true;
            }
        }

        if (!empty($onlyActions)) {
            if ($isAllowed && in_array($action, $onlyActions)) {
                return true;
            }
        }

        if (empty($onlyActions) && empty($exceptActions)) {
            if ($isAllowed) {
                return true;
            }
        }

        return false;
    }
}