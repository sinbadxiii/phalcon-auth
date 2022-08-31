<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use InvalidArgumentException;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use function array_column;
use function array_search;

/**
 * Class Memory
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Memory extends AbstractAdapter
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $credentials
     * @return AuthenticatableInterface|null
     */
    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        $providerStorage = $this->getProviderStorage();

        return $this->first($providerStorage, $credentials);
    }

    /**
     * @param $identifier
     * @return AuthenticatableInterface|null
     */
    public function retrieveById($identifier): ?AuthenticatableInterface
    {
        if (empty($this->model)) {
            throw new InvalidArgumentException("Сonfig with key 'model' is empty");
        }

        $userModel = $this->model;

        return ($userData = $this->getProviderStorage()[$identifier]) ? new $userModel($userData) : null;
    }

    /**
     * @param AuthenticatableInterface $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool
    {
        return (isset($this->config['passsword_crypted'])) ? $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        ) : $credentials['password'] === $user->getAuthPassword();
    }

    /**
     * @param array $providerStorage
     * @param array $credentials
     * @return AuthenticatableInterface|null
     */
    public function first(array $providerStorage, array $credentials): ?AuthenticatableInterface
    {
        $field = array_key_first($credentials) ?? "email";
        $term = $credentials[$field];

        $key = array_search($term, array_column($providerStorage, $field), true);

        if (empty($this->model)) {
            throw new InvalidArgumentException("Сonfig with key 'model' is empty");
        }

        $userModel = $this->model;

        if ($key !== false) {
            return new $userModel($providerStorage[$key] + ['id' => $key]);
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function getProviderStorage(): mixed
    {
        if (empty($this->getData())) {
            throw new InvalidArgumentException(
                "Data is empty"
            );
        }

        return $this->data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->config && !isset($this->config["data"])) {
            throw new InvalidArgumentException(
                "Сonfig key 'datа' with user data array empty or does not exist"
            );
        }

        if (!empty($this->config["data"])) {
            $this->data = $this->config["data"];
        }

        return $this->data;
    }

    /**
     * @param $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}