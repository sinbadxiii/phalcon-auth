<?php

namespace Sinbadxiii\PhalconAuth\Adapter;

/**
 * Class Memory
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Memory extends CollectionAdapterAbstract implements AdapterInterface
{
    protected function getData()
    {
        return $this->config->data->toArray();
    }
}