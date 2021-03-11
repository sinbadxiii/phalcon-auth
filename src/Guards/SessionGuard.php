<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guards;

use Sinbadxiii\PhalconAuth\Events\BeforeLogin;
use Sinbadxiii\PhalconAuth\Events\AfterLogin;
use Sinbadxiii\PhalconAuth\Events\EventInterface;
use Sinbadxiii\PhalconAuth\Events\Logout;
use Sinbadxiii\PhalconAuth\User\AuthenticatableInterface;
use Phalcon\Di;

/**
 * Class Auth
 * @package Sinbadxiii\PhalconAuth\Guard
 */
class SessionGuard
{
    use UserHelper;

    protected $name;
    protected $session;
    protected $cookies;
    protected $eventsManager;
    protected $request;

    public function __construct($name, $provider)
    {
        $this->name     = $name;
        $this->provider = $provider;

        $this->session        = Di::getDefault()->getShared("session");
        $this->cookies        = Di::getDefault()->getShared("cookies");
        $this->eventsManager  = Di::getDefault()->getShared("eventsManager");
        $this->request        = Di::getDefault()->getShared("request");
    }

    public function attempt(array $credentials = [], $remember = false)
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        return false;
    }

    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        if (!is_null($id)) {
            $this->user = $this->provider->retrieveById($id);
        }

        if (is_null($this->user) && !is_null($recaller = $this->recaller())) {

            $this->user = $this->userFromRecaller($recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());
            }
        }

        return $this->user;
    }

    protected function userFromRecaller($recaller)
    {
        return $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token(), $recaller->userAgent()
        );
    }

    protected function recaller()
    {
        if ($recaller = $this->getRememberData()) {
            return new UserRemember($recaller);
        }
    }

    protected function getRememberData()
    {
        if ($this->cookies->has($this->getRememberName())) {
            return $this->cookies->get($this->getRememberName())->getValue();
        }
    }

    public function getName()
    {
        return "auth_{$this->name}_" . sha1(static::class);
    }

    public function getRememberName()
    {
        return "remember_{$this->name}_" . sha1(static::class);
    }

    public function login(AuthenticatableInterface $user, $remember = false)
    {
        $this->event(new BeforeLogin());

        $this->updateSession($user->getAuthIdentifier());

        if ($remember) {
            $this->rememberUser($user);
        }

        $this->setUser($user);

        $this->event(new AfterLogin());
    }

    protected function rememberUser(AuthenticatableInterface $user)
    {
        $this->cookies->set($this->getRememberName(),
            json_encode([
                'id'         => $user->getAuthIdentifier(),
                'token'      => $user->getRememberToken()->token,
                'user_agent' => $this->request->getUserAgent()
            ], JSON_THROW_ON_ERROR)
        );

        $this->createRememberToken($user);
    }

    protected function createRememberToken(AuthenticatableInterface $user)
    {
        if (empty($user->getRememberToken())) {
            $this->provider->createRememberToken($user);
        }
    }

    protected function updateSession($id)
    {
        $this->session->set($this->getName(), $id);
    }

    public function logout()
    {
        $user = $this->user();

        if ($this->user && ($tokenRemember = $this->user->getRememberToken())) {
            $tokenRemember->delete();
        }

        $this->session->remove($this->getName());

        if (! is_null($this->recaller())) {
            $this->cookies->get($this->getRememberName())->delete();
        }

        $this->event(new Logout($user));

        $this->user = null;
    }

    public function event(EventInterface $event)
    {
        return $this->eventsManager->fire("auth:" . $event->getType(), $this);
    }
}