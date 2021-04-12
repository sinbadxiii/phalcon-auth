<?php

namespace Sinbadxiii\PhalconAuth\User;

use Sinbadxiii\PhalconAuth\RememberToken\RememberTokenModel;
use Phalcon\Di;
use Phalcon\Security\Random;

class UserModelProvider implements UserModelProviderInterface
{
    protected $model;

    protected $hasher;

    /**
     * UserModelProvider constructor.
     * @param $hasher
     * @param $model
     */
    public function __construct($hasher, $model)
    {
        $this->hasher = $hasher;
        $this->model  = $model;
    }

    public function retrieveByCredentials(array $credentials)
    {
        $builder = DI::getDefault()->get('modelsManager')
            ->createBuilder()
            ->from([$this->model]);

        foreach ($credentials as $key => $value) {
            if ($key === 'password') {
                continue;
            }

            $builder->andWhere("{$key} = :{$key}:", [$key => $value]);
        }

        return $builder->getQuery()->execute()->getFirst();
    }

    public function retrieveById($identifier)
    {
        /**
         * @todo придумать как не жестко привязывать к id
         */


        return $this->model::findFirst($identifier);



        return DI::getDefault()->get('modelsManager')
            ->createBuilder()
            ->from(['m' =>$this->model])
            ->where("m.id = :id:",
            [
                "id" => $identifier
            ])
            ->getQuery()->execute()->getFirst();
    }

    public function retrieveByToken($identifier, $token, $user_agent)
    {
        $retrievedModel = DI::getDefault()->get('modelsManager')
            ->createBuilder()
            ->from($this->model)
            ->where("id = :id:",
                [
                    "id" => $identifier
                ])
            ->getQuery()->execute()->getFirst();

        if (!$retrievedModel) {
            return;
        }

        $rememberTokenModel = $retrievedModel->getRememberToken($token);

        if (!$rememberTokenModel) {
            return;
        }

        $rememberToken = $rememberTokenModel->getToken();

        return $rememberToken && hash_equals($rememberToken, $token) && hash_equals(
            $rememberTokenModel->getUserAgent(), $user_agent
        ) ? $retrievedModel : null;
    }

    public function createRememberToken(AuthenticatableInterface $user)
    {
        $random = new Random();
        $token = $random->base64(60);

        $rememberToken = new RememberTokenModel();
        $rememberToken->token = $token;
        $rememberToken->user_agent = DI::getDefault()->get('request')->getUserAgent();
        $rememberToken->ip = DI::getDefault()->get('request')->getClientAddress();

        $user->setRememberToken($rememberToken);

        /**
         * @todo стоит ли тут вызывать ивент сохранения??
         */
        $user->save();

        return $rememberToken;
    }

    public function validateCredentials(AuthenticatableInterface $user, array $credentials)
    {
        return $this->hasher->checkHash(
            $credentials['password'], $user->getAuthPassword()
        );
    }
}