<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Phalcon\Http\Request;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\Adapter\AdapterWithRememberTokenInterface;
use InvalidArgumentException;
use Phalcon\Di\Di;

use function is_null;

/**
 * Class Session
 * @package Sinbadxiii\PhalconAuth\Guard
 */
class Session implements GuardInterface, GuardStatefulInterface, BasicAuthInterface
{
    use GuardHelper;
    use BasicHelper;

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
    protected $provider;

    public function __construct($name, $provider)
    {
        $this->name          = $name;
        $this->provider      = $provider;
        $this->session       = Di::getDefault()->getShared("session");
        $this->cookies       = Di::getDefault()->getShared("cookies");
        $this->eventsManager = Di::getDefault()->getShared("eventsManager");
        $this->request       = $this->getRequest();
    }

    public function attempt(array $credentials = [], $remember = false): bool
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

    protected function hasValidCredentials($user, $credentials): bool
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

    /**
     * @throws \JsonException
     */
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

    public function login(AuthenticatableInterface $user, $remember = false): void
    {
        $this->eventsManager->fire("auth:beforeLogin", $this);

        $this->updateSession($user->getAuthIdentifier());

        if ($remember) {

            if (!$this->provider instanceof AdapterWithRememberTokenInterface) {
                throw new InvalidArgumentException(
                    "Provider " . $this->provider::class . " not instanceof AdapterWithRememberTokenInterface"
                );
            }

            $this->rememberUser($user);
        }

        $this->setUser($user);

        $this->eventsManager->fire("auth:afterLogin", $this);
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
        $this->eventsManager->fire("auth:beforeLogin", $this);

        if ($this->validate($credentials)) {
            $this->setUser($this->lastUserAttempted);

            $this->eventsManager->fire("auth:afterLogin", $this);

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
                    'token'      => $rememberToken->getToken(),
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

        $this->eventsManager->fire("auth:beforeLogout", $this, [
            "user" => $user
        ]);

        $recaller = $this->recaller();

        if ($recaller !== null) {
            $tokenRemember = $user->getRememberToken($recaller->token());

            if ($tokenRemember) {
                $tokenRemember->delete();
            }

            $this->cookies->get($this->getRememberName())->delete();
        }

        $this->session->remove($this->getName());

        $this->eventsManager->fire("auth:afterLogout", $this, [
            "user" => $user
        ]);

        $this->user = null;
    }

    public function getLastUserAttempted()
    {
        return $this->lastUserAttempted;
    }

    public function viaRemember()
    {
        return $this->viaRemember;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRequest(): Request
    {
        return $this->request ?: Di::getDefault()->getShared("request");
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}