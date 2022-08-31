<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Phalcon\Events\AbstractEventsAware;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Http\Response\Cookies;
use Phalcon\Http\Request;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Sinbadxiii\PhalconAuth\Adapter\AdapterWithRememberTokenInterface;
use InvalidArgumentException;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;
use Phalcon\Session\ManagerInterface as SessionManagerInterface;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;;

use function is_null;

/**
 * Class Session
 * @package Sinbadxiii\PhalconAuth\Guard
 */
class Session extends AbstractEventsAware implements
    GuardInterface, GuardStatefulInterface, BasicAuthInterface, EventsAwareInterface
{
    use GuardHelper;
    use BasicHelper;

    /**
     * @var mixed
     */
    protected $session;

    /**
     * @var mixed
     */
    protected $cookies;

    /**
     * @var \Phalcon\Http\Request
     */
    protected $request;

    /**
     * @var EventsManagerInterface
     */
    protected $eventsManager;

    /**
     * @var null|AuthenticatableInterface
     */
    protected $lastUserAttempted;

    /**
     * If the user was an authenticate recaller
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    public function __construct(
        AdapterInterface $adapter, SessionManagerInterface $session,
        Cookies $cookies, Request $request, EventsManagerInterface $eventsManager)
    {
        $this->adapter       = $adapter;
        $this->session       = $session;
        $this->cookies       = $cookies;
        $this->request       = $request;
        $this->eventsManager = $eventsManager;
    }

    /**
     * @param array $credentials
     * @param $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false): bool
    {
        $this->lastUserAttempted = $this->adapter->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($this->lastUserAttempted, $credentials)) {
            $this->login($this->lastUserAttempted, $remember);

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     * @throws \JsonException
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        if (!is_null($id)) {
            $this->user = $this->adapter->retrieveById($id);
        }

        if (is_null($this->user) && !is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());
            }
        }

        return $this->user;
    }

    /**
     * @param $user
     * @param array $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, array $credentials): bool
    {
        return !is_null($user) && $this->adapter->validateCredentials($user, $credentials);
    }

    /**
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $this->lastUserAttempted = $this->adapter->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($this->lastUserAttempted, $credentials);
    }

    /**
     * @param $recaller
     * @return mixed
     */
    protected function userFromRecaller($recaller)
    {
        $this->viaRemember = ! is_null($user = $this->adapter->retrieveByToken(
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

    /**
     * @return mixed
     */
    protected function getRememberData()
    {
        if ($this->cookies->has($this->getRememberName())) {
            return $this->cookies->get($this->getRememberName())->getValue();
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {

        return "auth-" . sha1(static::class . $this->adapter::class);
    }

    /**
     * @return string
     */
    public function getRememberName(): string
    {
        return "remember_" . sha1(static::class . $this->adapter::class);
    }

    /**
     * @param AuthenticatableInterface $user
     * @param bool $remember
     * @return void
     * @throws \JsonException
     */
    public function login(AuthenticatableInterface $user, bool $remember = false): void
    {
        $this->eventsManager->fire("auth:beforeLogin", $this);

        $this->updateSession($user->getAuthIdentifier());

        if ($remember) {

            if (!$this->adapter instanceof AdapterWithRememberTokenInterface) {
                throw new InvalidArgumentException(
                    "Adapter " . $this->adapter::class . " not instanceof AdapterWithRememberTokenInterface"
                );
            }

            $this->rememberUser($user);
        }

        $this->setUser($user);

        $this->eventsManager->fire("auth:afterLogin", $this);
    }

    /**
     * @param $id
     * @param bool $remember
     * @return mixed
     * @throws \JsonException
     */
    public function loginById($id, bool $remember = false)
    {
        if ( ! is_null($user = $this->adapter->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    /**
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = []): bool
    {
        $this->eventsManager->fire("auth:beforeLogin", $this);

        if ($this->validate($credentials)) {
            $this->setUser($this->lastUserAttempted);

            $this->eventsManager->fire("auth:afterLogin", $this);

            return true;
        }

        return false;
    }

    /**
     * @param AuthenticatableInterface $user
     * @return void
     * @throws \JsonException
     */
    protected function rememberUser(AuthenticatableInterface $user): void
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

    /**
     * @param AuthenticatableInterface $user
     * @return RememberTokenInterface
     */
    protected function createRememberToken(AuthenticatableInterface $user): RememberTokenInterface
    {
        return $this->adapter->createRememberToken($user);
    }

    /**
     * @param $id
     * @return void
     */
    protected function updateSession($id): void
    {
        $this->session->set($this->getName(), $id);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function logout(): void
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

    /**
     * @return mixed
     */
    public function getLastUserAttempted()
    {
        return $this->lastUserAttempted;
    }

    /**
     * @return bool
     */
    public function viaRemember(): bool
    {
        return $this->viaRemember;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param SessionManagerInterface $session
     * @return $this
     */
    public function setSession(SessionManagerInterface $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * @param Cookies $cookies
     * @return $this
     */
    public function setCookies(Cookies $cookies)
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter): static
    {
        $this->adapter = $adapter;

        return $this;
    }
}