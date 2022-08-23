<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use InvalidArgumentException;
use Phalcon\Config\ConfigInterface;
use Phalcon\Encryption\Security;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

use function array_column;
use function array_keys;
use function array_map;
use function array_search;

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
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param Security $hasher
     * @param ConfigInterface $config
     */
    public function __construct(Security $hasher, ConfigInterface $config)
    {
        $this->hasher = $hasher;
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    abstract protected function getProviderStorage(): mixed;

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
        if (empty($this->config->model)) {
            throw new InvalidArgumentException("Сonfig with key 'model' is empty");
        }

        $userModel = $this->config->model;

        return ($userData = $this->getProviderStorage()[$identifier]) ? new $userModel($userData) : null;
    }

    /**
     * @param AuthenticatableInterface $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool
    {
        return ($this->config->passsword_crypted) ? $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        ) : $credentials['password'] === $user->getAuthPassword();
    }

    /**
     * @param array $providerStorage
     * @param array $credentials
     * @return array
     */
    public function find(array $providerStorage, array $credentials): array
    {
        $field = array_key_first($credentials) ?? "email";
        $term = $credentials[$field];

        $keys = array_keys(array_column($providerStorage, $field), $term);

        if (empty($this->config->model)) {
            throw new InvalidArgumentException("Сonfig with key 'model' is empty");
        }

        $userModel = $this->config->model;

        $result = [];

        if ($keys !== null) {
            $result = array_map(static function($key) use ($providerStorage, $userModel) {
                return new $userModel($providerStorage[$key] + ['id' => $key]);
            }, $keys);
        }

        return $result;
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

        if (empty($this->config->model)) {
            throw new InvalidArgumentException("Сonfig with key 'model' is empty");
        }

        $userModel = $this->config->model;

        if ($key !== false) {
            return new $userModel($providerStorage[$key] + ['id' => $key]);
        }

        return null;
    }
}