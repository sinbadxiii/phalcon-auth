# Phalcon Auth



You can see an example of an application with authorization and limit here [sinbadxiii/phalcon-auth-example](https://github.com/sinbadxiii/phalcon-auth-example)

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth/phalcon-auth-logo.png?raw=true)

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></img></a>
<a href="https://packagist.org/packages/sinbadxiii/phalcon-auth"><img src="https://img.shields.io/packagist/dt/sinbadxiii/phalcon-auth?style=flat-square" alt="Packagist Downloads"></img></a>
<a href="https://github.com/sinbadxiii/phalcon-auth/releases"><img src="https://img.shields.io/github/release/sinbadxiii/phalcon-auth?style=flat-square" alt="Latest Version"></img></a>
</p>

- ~~*Session and Cookie Based Authentication*~~
- ~~*Token Based Authentication*~~
- ~~*Extension with custom guards*~~
- ~~*Guest access to controllers*~~
- ~~*[Authentication with JWT](https://github.com/sinbadxiii/phalcon-auth-jwt)*~~
- ~~*HTTP Basic authentication*~~
- Activation by email (it is required to standardize work with mail)
- Password recovery (it is required to standardize work with mail)

Phalcon Auth will allow you to create an authentication system in your web application.

The general essence of an authentication system is to have "Guards" (Guard) and "Providers" (Provider) at hand, the guards determine how users will be authenticated, for example using standard Sessions, Session storage and Cookies.

Providers determine where users will be retrieved from. By default, this is, of course, Phalcon\Model and the database query builder.

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth/auth-scheme.webp?raw=true)

## Extended guards
* [JWT Guard](https://github.com/sinbadxiii/phalcon-auth-jwt)

## Installation

 | Phalcon 3   | Phalcon 4     | Phalcon 5     | Phalcon 6
 :-------------| :-------------| :-------------| :----------
 | :x:         | :heavy_check_mark:| :heavy_check_mark: | :question:

PHP ^7.4-8.0.

Require the project using composer:

`composer require "sinbadxiii/phalcon-auth:^v1.1.0"`

## How use

### 1. Config

Your application's authentication configuration file will either need to be located in your config folder, such as config/auth.php or global config.php file with access by "auth" key (`$this->config->auth`).

```php
<?php

...
    'auth' => [
        'defaults' => [
            'guard' => 'web',
            'passwords' => 'users',
        ],
        'guards' => [
            'web' => [
                'driver' => 'session', //session || token
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'driver' => 'model',
                'model'  => \Models\Users::class,
            ]
        ]
    ],
...
```

#### 1.1. Config Session

With the default auth configuration - `defaults => guard => 'web'` and `driver => 'session'`, the user enters their username and password using the login form.

If these credentials are correct, the application will store information about the authenticated user in the user's session.

The cookie sent to the browser contains the session ID so that subsequent requests to the application can associate the user with the correct session. After receiving the session cookie, the application will retrieve the session data based on the session id, note that the authentication information will be stored in the session, and will consider the user to be "authenticated".

```php
<?php

...
    'auth' => [
        'defaults' => [
            'guard' => 'web',
            'passwords' => 'users',
        ],
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'driver' => 'model',
                'model'  => \Models\Users::class,
            ]
        ]
    ],
...
```

#### 1.2. Config Token

If the auth configuration is set to `defaults => guard => 'api'` and `driver => 'token'`, this setting will allow authentication to access your API application, cookies are usually not used for authentication due to lack of web -browser. Instead, the remote service sends an API token on every request. The application can validate the incoming token against a table of valid API tokens and "authenticate" the request as being made by the user associated with that API token.

```php
<?php

...
    'auth' => [
        'defaults' => [
            'guard' => 'api',
            'passwords' => 'users',
        ],
        'guards' => [
            'api' => [
                'driver' => 'token',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'driver' => 'model',
                'model'  => \Models\Users::class,
            ]
        ]
    ],
...
```

By default, the name of the parameter in the request and the field in the database is equal to `auth_token`,

for example GET request:

```
//GET
https://yourapidomain/api/v2/users?auth_token=fGa$gdGPSfEgT41r3F4fg#^33
```

or POST:

```
//POST
//params POST request 
[
    "auth_token": "fGa$gdGPSfEgT41r3F4fg#^33"
]

https://yourapidomain/api/v2/users
```

or `Authorization` header:

```

Authorization: Bearer fGa$gdGPSfEgT41r3F4fg#^33

https://yourapidomain/api/v2/users
```

### 2. Database

Import files to create tables in the database `db/users.sql`, `db/users_remember_tokens.sql` and `db/create_auth_token_users.sql` if the auth_token field is needed

### 3. Register a service provider

By default `Sinbadxiii\PhalconAuth\Auth()` will use auth config from `$this->config->auth`.

```php 
$di->setShared('auth', function () {
    return new \Sinbadxiii\PhalconAuth\Auth();
});
```

Or explicitly pass your auth config:

```php 
$di->setShared('auth', function () {
    $authConfig = $this->getConfig()->get("auth");
    
    return new \Sinbadxiii\PhalconAuth\Auth($authConfig);
});
```

### 4. Controller access, authentication or guest 

Implement your controllers from `Sinbadxiii\PhalconAuth\Middlewares\Accessicate`. 
You can use the `authAccess()` method on a controller to indicate if the controller needs authenticated access.

```php 
declare(strict_types=1);

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use Sinbadxiii\PhalconAuth\Middlewares\Accessicate;

class ProfileController extends Controller implements Accessicate
{
    ...
    public function authAccess(): bool
    {
       return true; //or false, if you don't need to check authentication
    }  
    ...
}
```

or just use the `$authAccess` property in the controller, adjusting with the help of `false` and `true` access to the controller

```php 
declare(strict_types=1);

namespace App\Controllers;

use Phalcon\Mvc\Controller;

class ProfileController extends Controller
{
    //access to the controller only when the user is logged in
    public $autAccess = true;
}
```


### 5. Middleware 

Create middleware extends from `Sinbadxiii\PhalconAuth\Middlewares\Authenticate`
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
     * @return \Phalcon\Http\ResponseInterface|void
     */
    protected function redirectTo()
    {
        if (isset($this->response)) {
            $this->response->redirect("/login")->send();
        }
    }
}
```

and attach it in your service dispatcher:

```php

use App\Security\Authenticate;

... 
 
$di->setShared("dispatcher", function () use ($di) {
    $dispatcher = new Dispatcher();

    $eventsManager = $di->getShared('eventsManager');
    $eventsManager->attach('dispatch', new Authenticate());
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});
```

When the middleware detects an unauthenticated user, it executes the `redirectTo()` method, by default the redirect goes to the url you need (the same login form, for example), you can change this behavior, for example, return a json response if an ajax request is used for authentication:

```php

protected function redirectTo()
{
    $this->response->setJsonContent(
        [
            'success' => false,
            'message' => 'Authentication failure'
        ], JSON_UNESCAPED_UNICODE
    );

    if (!$this->response->isSent()) {
        $this->response->send();
    } 
}
```

### 6. Users 

Implement your model Users fom `Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface` and `Sinbadxiii\PhalconAuth\Contracts\RememberingInterface` (if you want to use the remember me feature), example:

```php 
namespace Models;

use Phalcon\Di\Di;
use Sinbadxiii\PhalconAuth\RememberToken\RememberTokenModel;
use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\Contracts\RememberingInterface;
use Sinbadxiii\PhalconAuth\Contracts\RememberTokenInterface;

class Users extends BaseModel implements AuthenticatableInterface, RememberingInterface
{  
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
    
    public function setPassword(string $password)
    {
        $this->password = Di::getDefault()->getShared("security")->hash($password);
        return $this;
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

    /**
    * @param string|null $token
    * @return RememberTokenInterface|null|false
    */
    public function getRememberToken(string $token = null): ?RememberTokenInterface
    {
        return $this->getRelated('remember_token', [
            'token=:token:',
            'bind' => ['token' => $token]
        ]);
    }

    public function setRememberToken(RememberTokenInterface $value)
    {
        $this->remember_token = $value;
    }
}
```

## Methods

### Checking the authentication of the current user

To determine if the user making the incoming HTTP request is authenticated, you can use the `check()` method. This method will return `true` if the user is authenticated:

```php
$this->auth->check(); 
//check authentication
```

for example, you can check on the login form page that if the user is logged in, then do not show him the input form:


```php
public function loginFormAction()
{
    if ($this->auth->check()) { 
        //redirect to profile page 
        return $this->response->redirect(
            "/profile", true
        );
    }
}
```

### Getting the authenticated user

When processing an incoming request, you can access the authenticated user through the `user()` method. The result will be the provider specified in the auth.php config, according to the Phalcon\Model Users standard of the users table.

You can also request a user identifier (ID) using the `id()` method:


```php 
$this->auth->user(); //get the user

$this->auth->id(); //get user id
```

### Authentication attempt

The `attempt()` method is used to handle authentication attempts from your application's login form:

```php 
$username = $this->request->getPost("username");
$password = $this->request->getPost("password");

//attempt login with credentials
if ($this->auth->attempt(['username' => $username, 'password' => $password])) {

 //success attempt
 ...
}

//fail attempt
```

The `attempt()` method takes an array of key/value pairs as its first argument. The values in the array will be used to look up the user in the users database table. So, in the example above, the user will be retrieved by the value of the username column. If the user is found, the hashed password stored in the database will be compared against the password value passed to the method. You should not hash the value of the incoming password request, as the password is already automatically hashed to compare it with the hashed password in the database. An authenticated session will be started for the user if the hashed passwords match.

Remember that users from your database will be queried based on the "provider" configuration. The default auth configuration specifies the user provider = "model" (Phalcon\Model) and is instructed to use the \Models\User model to get users. You can change these values in the configuration file depending on the needs of your application.

The `attempt()` method will return `true` if the authentication was successful. Otherwise, `false` will be returned.

### Specifying additional credentials

You can also add additional request credentials in addition to the user's email/username and password. To do this, simply add the request conditions to the array passed to the `attempt()` method. For example, we can check if a user is marked as "is_published":
```php 
$username = $this->request->getPost("username");
$password = $this->request->getPost("password");

//attempt login with additional credentials
if ($this->auth->attempt(['username' => $username, 'password' => $password, 'is_published' => 1])) {

 //success attempt
 ...
}

//fail attempt
```

### "Remember me"

If you want to provide "remember me" functionality in your application, you can pass a boolean value as the second argument to the attempt method.

When this value is true, the user will be authenticated indefinitely or until the user logs out manually by logout. The `users_remember_tokens` table contains a token string column that will be used to store the "remember me" token:

```php 
$username = $this->request->getPost("username");
$password = $this->request->getPost("password");
$remember = this->request->getPost('remember') ? true : false;

//attempt login with credentials and remember
if ($this->auth->attempt(['username' => $username, 'password' => $password], $remember)) {

 //success attempt
 ...
}

//fail attempt
```

Use the `viaRemember()` method to check if the user is authenticated with the "remember me" cookie:

```php
//use method viaRemember to check the user was authenticated using the remember me cookie
$this->auth->viaRemember();
```

### Аутентифицировать пользовательский экземпляр

Если вам нужно установить существующий пользовательский экземпляр в качестве текущего аутентифицированного пользователя, вы можете передать пользовательский экземпляр методу `login()`. Данный пользовательский экземпляр должен быть реализацией Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface.

Этот метод аутентификации полезен, когда у вас уже есть действующий экземпляр пользователя, например, сразу после регистрации пользователя в вашем приложении:

```php
$user = Users::findFirst(1);
// Login and Remember the given user
$this->auth->login($user, $remember = true);
```

### Аутентифицировать пользователя по идентификатору

Для аутентификации пользователя с использованием первичного ключа записи в базе данных вы можете использовать метод `loginById()`. Этот метод принимает первичный ключ пользователя, которого вы хотите аутентифицировать:

```php
//and force login user by id 
$this->auth->loginById(1, true);
```

### Аутентифицировать пользователя один раз

Используя метод `once()` вы можете аутентифицировать пользователя в приложении для одного запроса. При вызове этого метода не будут использоваться сессии или файлы cookie:

```php
//once auth without saving session and cookies
$username = $this->request->getPost("username");
$password = $this->request->getPost("password");
$this->auth->once(['username' => $username, 'password' => $password]);
```

## Выход

Чтобы вручную разлогинить пользователя из вашего приложения, вы можете использовать метод `logout()`. После этого удалится вся информация об аутентификации из сессии пользователя, так что последующие запросы уже не будут аутентифицированы.

```php

$this->auth->logout();
//log out user 
```

## HTTP Basic Authentication

[Базовая аутентификация HTTP](https://en.wikipedia.org/wiki/Basic_access_authentication) обеспечивает быстрый способ аутентификации пользователей вашего приложения без настройки специальной страницы «входа в систему». Достаточно передать в заголовке `Authorization`, значение `Basic` и пары емейл (либо другое поле пользователя) и пароль, разделенные двоеточием и закодированые `base64_encode()` 

Для начала создайте middleware типа AuthBasic с методом `$this->auth->basic("email")`и прикрепите к сервис-провайдеру dispatcher, как было указано выше.

Аргумент `email` указывает на то, что поиск пользователя будет осуществляться по полям email и password. Указав другое поле, например `username`, поиск будет осуществляться по паре username и password.

```php
$di->setShared("dispatcher", function () use ($di) {
    $dispatcher = new Dispatcher();

    $eventsManager = $di->getShared('eventsManager');
    $eventsManager->attach('dispatch', new AuthenticateWithBasic());
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});
```

Пример, использования

```php 
<?php

declare(strict_types=1);

namespace App\Security;

use Sinbadxiii\PhalconAuth\Middlewares\Authenticate as AuthMiddleware;

/**
 * Class Authenticate
 * @package App\Security
 */
class AuthenticateWithBasic extends AuthMiddleware
{
    /**
     * @var
     */
    protected $message;

    /**
     * @return bool
     */
    protected function authenticate()
    {
        try {
            if ($this->auth->basic("email") || $this->isGuest()) {
                return true;
            }
        } catch (\Throwable $e) {
            $this->message = $e->getMessage();
        } 
        $this->unauthenticated();
    }

    /**
     * @return \Phalcon\Http\ResponseInterface|void
     */
    protected function redirectTo()
    {
        if (isset($this->response)) {
            $this->response->setJsonContent(
                [
                    'message' => $this->message ?? "Unauthorized Error"
                ]
            )->setStatusCode(401)->send();
        }
    }
}

```

После запроса, в сессию запишется пользователь, и последующие запросы, могут уже не содержать пользовательские данные в заголовке `Authorization`, до тех пор пока сессия не "стухнет".

### Basic HTTP-аутентификация без сохранения состояния

Вы можете использовать базовую аутентификацию HTTP без сохранения пользователя в сессии. Это в первую очередь полезно, если вы решите использовать HTTP-аутентификацию для аутентификации запросов к API вашего приложения. Для этого определите промежуточное программное обеспечение, которое вызывает метод `onceBasic()`, например:

```php 
<?php

declare(strict_types=1);

namespace App\Security;

use Sinbadxiii\PhalconAuth\Middlewares\Authenticate as AuthMiddleware;

/**
 * Class Authenticate
 * @package App\Security
 */
class AuthenticateWithBasic extends AuthMiddleware
{
    /**
     * @var
     */
    protected $message;

    /**
     * @return bool
     */
    protected function authenticate()
    {
        try {
            if ($this->auth->onceBasic("email") || $this->isGuest()) {
                return true;
            }
        } catch (\Throwable $e) {
            $this->message = $e->getMessage();
        }
        $this->unauthenticated();
    }

    /**
     * @return \Phalcon\Http\ResponseInterface|void
     */
    protected function redirectTo()
    {
        if (isset($this->response)) {
            $this->response->setJsonContent(
                [
                    'message' => $this->message ?? "Unauthorized Error"
                ]
            )->setStatusCode(401)->send();
        }
    }
}
```
После запроса, ни куки, ни сессия не будут содержать данные о пользователе, и следущий запрос так же должен содержать пользовательские данные заголовка `Authorization`, иначе будет вызвано исключение `Sinbadxiii\PhalconAuth\Exceptions\UnauthorizedHttpException;`

### Configuration

Copy file from `config/auth.php` in your folder config and merge your config

```php 

...
'auth' => [
        'defaults' => [
            'guard' => 'web',
            'passwords' => 'users',
        ],
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'driver' => 'model',
                'model'  => \Models\Users::class,
            ],
//          'users' => [
//               'driver' => 'file',
//               'path'  => __DIR__ . "/users.json",
//               'passsword_crypted' => false
//          ],
        ]
    ],
..

```

Если в качестве источника пользователей будет выбрана не `model`, а `file`, то необходимо будет указать путь к .json файлу в `path`, формата например:

```json
 {
    "0":{"name":"admin","password": "admin","email": "admin@admin.ru"},
    "1":{"name":"admin1","password": "admin1","email": "admin1@admin1.ru"}
 }
```

или если включено шифрование паролей в `password_crypted`, то указывать пароль в зашифрованном виде:

```json
 {
   "0":{"name":"admin1","password": "$2y$10$ME02QlQxWGdDNUdiUTJucuhQHYQlIglb3lG2rfdzvK3UbQXAPrc.q","email": "admin1@admin1.ru"}
 }
```

Шифровать пароль необходимо будет с помощью `$this->security->hash()`, который вы используете у себя в приложении. 


### License
The MIT License (MIT). Please see [License File](https://github.com/sinbadxiii/phalcon-auth/blob/master/LICENSE) for more information.