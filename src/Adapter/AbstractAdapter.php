<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Phalcon\Encryption\Security;

/**
 * Class CollectionAdapterAbstract
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var Security
     */
    protected Security $hasher;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var mixed
     */
    protected $model;

    /**
     * @param Security $hasher
     * @param array $config
     */
    public function __construct(Security $hasher, array $config = [])
    {
        $this->hasher = $hasher;
        $this->config = $config;

        if (!empty($config['model'])) {
            $this->model = $config['model'];
        }
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return $this
     */
    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }
}