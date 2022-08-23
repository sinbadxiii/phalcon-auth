<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use InvalidArgumentException;

/**
 * Class Memory
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Memory extends AbstractAdapter
{
    /**
     * @return mixed
     */
    protected function getProviderStorage(): mixed
    {
        if (!$this->config->has("data")) {
            throw new InvalidArgumentException(
                "Сonfig key 'datа' with user data array empty or does not exist"
            );
        }

        return $this->config->data->toArray();
    }
}