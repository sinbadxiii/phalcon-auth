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
    protected $lastUserAttempted;
    /**
     * If the user was an authenticate recaller
     *
     * @var bool
     */
    protected $viaRemember = false;

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
        $this->lastUserAttempted = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($this->lastUserAttempted, $credentials)) {
            $this->login($this->lastUserAttempted, $remember);

            return true;
        }

        return false;
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

    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    public function validate(array $credentials = [])
    {
        $this->lastUserAttempted = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($this->lastUserAttempted, $credentials);
    }

    protected function userFromRecaller($recaller)
    {
        $this->viaRemember = ! is_null($user = $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token(), $recaller->userAgent()
        ));

        return $user;
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

    public function loginById($id, $remember = false)
    {
        if ( ! is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    public function once(array $credentials = [])
    {
        $this->event(new BeforeLogin());

        if ($this->validate($credentials)) {
            $this->setUser($this->lastUserAttempted);

            $this->event(new AfterLogin());

            return true;
        }

        return false;
    }

    protected function rememberUser(AuthenticatableInterface $user)
    {
        $rememberToken = $this->createRememberToken($user);

        if (!is_null($rememberToken)) {
            $this->cookies->set($this->getRememberName(),
                json_encode([
                    'id'         => $user->getAuthIdentifier(),
                    'token'      => $rememberToken->token,
                    'user_agent' => $this->request->getUserAgent()
                ], JSON_THROW_ON_ERROR),
                date("U") + 360 * 24 * 60 * 60
            );
        }


    }

    protected function createRememberToken(AuthenticatableInterface $user)
    {
        return $this->provider->createRememberToken($user);
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

    public function getLastUserAttempted()
    {
        return $this->lastUserAttempted;
    }

    public function event(EventInterface $event)
    {
        return $this->eventsManager->fire("auth:" . $event->getType(), $this);
    }

    public function viaRemember()
    {
        return $this->viaRemember;
    }
}