# Phalcon Auth



You can see an example of an application with authorization and limit here [sinbadxiii/phalcon-auth-example](https://github.com/sinbadxiii/phalcon-auth-example)

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth/phalcon-auth-logo.png?raw=true)

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></img></a>
<a href="https://packagist.org/packages/sinbadxiii/phalcon-auth"><img src="https://img.shields.io/packagist/dt/sinbadxiii/phalcon-auth?style=flat-square" alt="Packagist Downloads"></img></a>
<a href="https://github.com/sinbadxiii/phalcon-auth/releases"><img src="https://img.shields.io/github/release/sinbadxiii/phalcon-auth?style=flat-square" alt="Latest Version"></img></a>
</p>


## Installation

Phalcon 4. PHP 7.2-8.0.

Require the project using composer:

`composer require "sinbadxiii/phalcon-auth:^v1.1.0"`


## How use

1. register service provider `Sinbadxiii\PhalconAuth\AuthProvider`

```php
declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class AuthProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $providerName = 'auth';

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $di->setShared($this->providerName, function () {
            return new Auth();
        });
    }
}
```

2. implement your controllers from `Sinbadxiii\PhalconAuth\Middlewares\Accessicate`

3. create middleware extends from `Sinbadxiii\PhalconAuth\Middlewares\Authenticate`

example:

```php
declare(strict_types=1);

namespace App\Security;

use Sinbadxiii\PhalconAuth\Middlewares\Authenticate as AuthMiddleware;

/**
 * Class Authenticate
 * @package App\Security
 */
class Authenticate extends AuthMiddleware
{
    /**
     * @param $event
     * @param $dispatcher
     */
    public function beforeDispatch($event, $dispatcher)
    {
        $controller = $dispatcher->getControllerClass();

        $this->setGuest(!(new $controller)->authAccess());
    }

    /**
     * @return \Phalcon\Http\ResponseInterface|void
     */
    protected function redirectTo()
    {
        if (isset($this->response)) {
            $this->response->redirect("/admin/login")->send();
        }
    }
}
```

and attach it in your dispatcher:

```php
declare(strict_types=1);

namespace App\Providers;

use App\Security\Authenticate;
use Phalcon\Mvc\Dispatcher;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class DispatcherProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    protected $providerName = 'dispatcher';

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di): void
    {
        $di->setShared($this->providerName, function () use ($di) {
            $dispatcher = new Dispatcher();

            $eventsManager = $di->getShared('eventsManager');
            $eventsManager->attach('dispatch', new Authenticate());
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });
    }
}
```

4. Implement your model users, example:

```php 
namespace Models;

use Sinbadxiii\PhalconAuth\RememberToken\RememberTokenModel;
use Sinbadxiii\PhalconAuth\User\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberToken\RememberingInterface;

class Users extends BaseModel implements AuthenticatableInterface, RememberingInterface
{
   ...
   
    public function initialize()
    {
        $this->setSource('users');

        $this->hasOne(
            'id',
            RememberTokenModel::class,
            'user_id',
            [
                'alias' => "remember_token"
            ]
        );
        $this->keepSnapshots(true);
    }
    
   ...

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken(string $token = null)
    {
        return $this->getRelated('remember_token', [
            'token=:token:',
            'bind' => ['token' => $token]
        ]);
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }
}

```

```php
$this->auth->check(); 
//check authorization

$this->user();
//get the user

$this->id();
//get user id

$this->auth->logout();
//log out user


$username = $this->request->getPost("username");
$password = $this->request->getPost("password");
$remember = $this->request->getPost("remember");

$this->auth->attempt(['username' => $username, 'password' => $password], $remember);
//attempt login with credentials


$user = Users::findFirst(1);
// Login and Remember the given user
$this->auth->login($user, true);
//and force login user by id 
$this->auth->loginById(1, true);


//once auth without saving session and cookies
$username = $this->request->getPost("username");
$password = $this->request->getPost("password");
$this->auth->once(['username' => $username, 'password' => $password]);

//use method viaRemember to check the user was authenticated using the remember me cookie
$this->auth->viaRemember();


```

### Configuration

copy file from `config/auth.php` in your folder config and merge yout config


### License
The MIT License (MIT). Please see [License File](https://github.com/sinbadxiii/phalcon-auth/blob/master/LICENSE) for more information.