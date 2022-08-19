<?php

namespace Sinbadxiii\PhalconAuth\Adapter;

/**
 * Class Memory
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Memory extends CollectionAdapterAbstract implements AdapterInterface
{
    /**
     * @return array
     */
    protected function getData(): array
    {
        return $this->config->data->toArray();
    }
}