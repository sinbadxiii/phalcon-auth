<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Access;

/**
 * Interface for Sinbadxiii\PhalconAuth\Access
 */
interface AccessInterface
{
    public function setExceptActions(...$actions): void;
    public function setOnlyActions(...$actions): void;
    public function isAllowed(string $actionName): bool;
    public function redirectTo();
    public function allowedIf(): bool;
}