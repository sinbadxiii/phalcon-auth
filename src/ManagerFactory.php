<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use Closure;
use InvalidArgumentException;
use Phalcon\Encryption\Security;
use Phalcon\Di\Di;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\ManagerInterface as SessionManagerInterface;
use Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;
use Sinbadxiii\PhalconAuth\Guard\GuardInterface;
use Sinbadxiii\PhalconAuth\Guard\Session;
use Sinbadxiii\PhalconAuth\Guard\Token;
use Phalcon\Events\EventsAwareInterface;

use function class_exists;
use function is_null;
use function call_user_func;

/**
 * Class Factory
 * @package Sinbadxiii\PhalconAuth
 */
class ManagerFactory extends Manager implements EventsAwareInterface
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * @var Security
     */
    protected Security $security;

    /**
     * @var array
     */
    protected array $customGuards = [];

    /**
     * @var array
     */
    protected array $customAdapters = [];

    protected SessionManagerInterface $session;
    protected Cookies $cookies;
    protected Request $request;
    protected EventsManagerInterface $eventsManager;

    /**
     * @param array $config
     * @param Security|null $security
     * @param SessionManagerInterface|null $session
     * @param Cookies|null $cookies
     * @param Request|null $request
     * @param EventsManagerInterface|null $eventsManager
     */
    public function __construct(
        array $config = [],
        Security $security = null,
        SessionManagerInterface $session = null,
        Cookies $cookies = null,
        Request $request = null,
        EventsManagerInterface $eventsManager = null
    ) {
        $this->config = $config;

        if (empty($this->config)) {
            if ($authConfig = Di::getDefault()->getShared("config")->auth) {

                if (empty($authConfig)) {
                    throw new InvalidArgumentException(
                        "Configuration file auth.php (or key config->auth into your config) does not exist"
                    );
                }

                $this->config = $authConfig->toArray();
            }
        }

        $this->security = $security ?? Di::getDefault()->getShared("security");
        $this->session = $session ?? Di::getDefault()->getShared("session");
        $this->cookies = $cookies ?? Di::getDefault()->getShared("cookies");
        $this->request = $request ?? Di::getDefault()->getShared("request");
        $this->eventsManager = $eventsManager ?? Di::getDefault()->getShared("eventsManager");
    }

    /**
     * @param string $name
     * @return array|null
     */
    protected function getConfigGuard(string $name): ?array
    {
        return $this->config['guards'][$name];
    }

    /**
     * @param string|null $name
     * @return GuardInterface
     */
    public function guard(?string $name = null): GuardInterface
    {
        $name = $name ?: $this->getDefaultGuardName();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function resolve(string $nameGuard)
    {
        $configGuard = $this->getConfigGuard($nameGuard);

        if (is_null($configGuard)) {
            throw new InvalidArgumentException("Auth guard [{$nameGuard}] is not defined.");
        }

        $providerAdapter = $this->getAdapterProvider($configGuard['provider']);

        if (isset($this->customGuards[$configGuard['driver']])) {
            return call_user_func(
                $this->customGuards[$configGuard['driver']],
                $providerAdapter,
                $configGuard
            );
        }

        switch ($configGuard['driver']) {
            case 'session':

                return new Session(
                    $providerAdapter, $this->session, $this->cookies,
                    $this->request, $this->eventsManager
                );
            case 'token':
                $configGuard['inputKey'] ??= "auth_token";
                $configGuard['storageKey'] ??= "auth_token";

                return new Token(
                    $providerAdapter,
                    $configGuard,
                    $this->request
                );
            default:
                throw new InvalidArgumentException(
                    "Auth driver [{$configGuard['driver']}] for guard [{$nameGuard}] is not defined."
                );
        }
    }

    /**
     * @param string|null $provider
     * @return mixed|AdapterInterface|void
     */
    public function getAdapterProvider(string $provider = null)
    {
        $configProvider = $this->config['providers'][$provider];

        if ($configProvider === null) {
            throw new InvalidArgumentException(
                "Config adapter '" . $provider . "' not defined"
            );
        }

        $adapterName = $configProvider['adapter'];

        if ($adapterName === null) {
            throw new InvalidArgumentException(
                "Adapter not assigned in config->auth->providers->" . $provider . "->adapter = ?"
            );
        }

        if (isset($this->customAdapters[$adapterName])) {
            return call_user_func(
                $this->customAdapters[$adapterName],
                $this->security,
                $configProvider
            );
        }

        $adapterClass = sprintf("\\Sinbadxiii\\PhalconAuth\\Adapter\\%s",
            ucfirst($adapterName)
        );

        if (!class_exists($adapterClass)) {
            throw new \InvalidArgumentException($adapterClass . " not found");
        }

        $adapter = new $adapterClass(
            $this->security,
            $configProvider
        );

        if (!($adapter instanceof AdapterInterface)) {
            throw new \InvalidArgumentException($adapterClass . " not implementing AdapterInterface");
        }

        return $adapter;
    }

    /**
     * @return string
     */
    public function getDefaultGuardName(): string
    {
        return $this->config['defaults']['guard'];
    }

    /**
     * @param $driver
     * @param Closure $callback
     * @return $this
     */
    public function extendGuard($driver, Closure $callback): ManagerInterface
    {
        $this->customGuards[$driver] = $callback;

        return $this;
    }

    /**
     * @param $name
     * @param Closure $callback
     * @return $this
     */
    public function extendProviderAdapter($name, Closure $callback): ManagerInterface
    {
        $this->customAdapters[$name] = $callback;

        return $this;
    }

    /**
     * @return EventsManagerInterface|null
     */
    public function getEventsManager(): ?EventsManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * @param EventsManagerInterface $eventsManager
     * @return void
     */
    public function setEventsManager(EventsManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }
}