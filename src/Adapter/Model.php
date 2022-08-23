<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Phalcon\Di\Di;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

/**
 * Class Model
 * @package Sinbadxiii\PhalconAuth\Adapter
 */
class Model extends AbstractAdapter implements AdapterWithRememberTokenInterface
{
    /**
     * @return mixed
     */
    protected function getProviderStorage(): mixed
    {
        return $this->config->model;
    }

    /**
     * @param array $credentials
     * @return mixed
     */
    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        $builder = Di::getDefault()->get('modelsManager')
            ->createBuilder()
            ->from([$this->getProviderStorage()]);

        foreach ($credentials as $key => $value) {
            if ($key === 'password') {
                continue;
            }

            $builder->andWhere("{$key} = :{$key}:", [$key => $value]);
        }

        return $builder->getQuery()->execute()->getFirst();
    }

    /**
     * @param $identifier
     * @return AuthenticatableInterface|null
     */
    public function retrieveById($identifier): ?AuthenticatableInterface
    {
        return $this->getProviderStorage()::findFirst($identifier);
    }

    /**
     * @param $identifier
     * @param $token
     * @param $user_agent
     * @return void|null
     */
    public function retrieveByToken($identifier, $token, $user_agent): ?AuthenticatableInterface
    {
        $retrievedModel = $this->getProviderStorage()::findFirst($identifier);

        if (!$retrievedModel) {
            return null;
        }

        $rememberTokenModel = $retrievedModel->getRememberToken($token);

        if (!$rememberTokenModel) {
            return null;
        }

        $rememberToken = $rememberTokenModel->getToken();

        return $rememberToken && hash_equals($rememberToken, $token) && hash_equals(
            $rememberTokenModel->getUserAgent(), $user_agent
        ) ? $retrievedModel : null;
    }

    /**
     * @param RememberingInterface $user
     * @return RememberTokenInterface
     */
    public function createRememberToken(RememberingInterface $user): RememberTokenInterface
    {
        return $user->createRememberToken();
    }

    /**
     * @param AuthenticatableInterface $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool
    {
        return $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}