# Phalcon Auth

You can see an example of an application with authorization here [sinbadxiii/phalcon-auth-example](https://github.com/sinbadxiii/phalcon-auth-example)

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

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth/auth-scheme.webp?raw=true)

## Extended guards
* [JWT Guard](https://github.com/sinbadxiii/phalcon-auth-jwt)

## Phalcon version

> Unfortunately version 2 of the library no longer supports Phalcon 4.

| Phalcon 3   | Phalcon 4     | Phalcon 5     | Phalcon 6
 :-------------| :-------------| :-------------| :----------
| :x:         | :x:| :heavy_check_mark: | :question:

## PHP are supported

^7.4-8.1.

## Install

Require the project using composer:

`composer require "sinbadxiii/phalcon-auth:^v2.0.0"`

## Introduction

Phalcon Auth позволит вам создать систему аутентификации в вашем веб-приложении.

Система аутентификации имеет такие понятия как «Охранники» (Guard) и «Поставщики» (Provider), охранники определяют, как пользователи будут аутентифицироваться, например, используя стандартные Хранилища Сессии и файлов куки.

Провайдеры определяют, какие данные будут браться в качестве пользователей, и так же откуда будут извлекаться эти пользователи. Откуда будт извлекаться данные пользователей определяют Адаптеры (Adapter). По умолчанию это обычно `model` - `Phalcon\Model` и построитель запросов к базе данных.

Кроме того есть другие варианты адаптеров: stream - файл, memory - массив с данными. Можно создать свой адаптер, реализуя интерфейс адаптера. Об этом поговорим чуть позже. 

> Guards и Providers не следует путать с «ролями» и «разрешениями» [ACL](https://docs.phalcon.io/4.0/en/acl). Auth и ACL  следует использовать вместе, если требуется более точная надстройка доступа к узлам приложения. Например использовать роль `admin`.

## Быстрый старт

Полностью пример готового приложения с аутентификацией доступен по адресу [sinbadxiii/phalcon-auth-example](https://github.com/sinbadxiii/phalcon-auth-example). Это типовой проект на Phalcon, который можно использовать как старт нового приложения, либо же просто ознакомиться с возможностями аутентификации на примере данного приложения.  

## Логика работы

Общий принцип работы аутентификации заключается в том, что пользователь вводит свое имя пользователя и пароль через форму входа. Если эти учетные данные верны, приложение сохранит информацию об аутентифицированном пользователе в сессии пользователя и будет считать пользователя "аутентифицированным". В случае использования "Запомнить меня" может быть создан файл cookie, который содержит идентификатор сессии, чтобы последующие запросы к приложению могли быть связаны с нужным пользователем. После получения идентификатора сессии из файла cookie приложение извлечет данные сессии из данных пользователя.

Возьмем другой случай, когда удаленному сервису необходимо пройти аутентификацию для доступа к API, обычно файлы cookie не используются для аутентификации, поскольку веб-браузер отсутствует. Вместо этого удаленная служба отправляет токен API при каждом запросе. Приложение может проверить входящий токен по таблице действительных токенов API и "аутентифицировать" запрос как выполненный пользователем, связанным с этим токеном API.

## Подготовка базы данных

По умолчанию конфигурационный файл имеет поставщика пользователей `users`, используя в качестве адаптера данных модель `App\Models\User` в папке `app/Models` вашего приложения. Т.е. для модели User у вас должна быть создана таблица `users`.

Если вы будет использовать функцию "Запомнить меня" - `RememberMe`, которая позволяет хранить сеанс аутентификации пользователя длительное время, то вам понадобится таблица `users_remember_tokens`, ну и соответственно ее модель в виде `App\Models\RememberToken`.

Для быстрого создания таблиц вы можете импортировать файлы из папки `db/users.sql`, `db/users_remember_tokens.sql`, а так же `db/create_auth_token_users.sql`, если будете использовать в качестве Guard - Token, которому необходимо поле `auth_token` для корректной работы.

### Пример конфигурационного файла для использования Сессий 

Итак, типичный пример конфигурационного файла, вашего приложения. Файл может находится в папке конфигов `config/auth.php` или в глобальном файле config.php с доступом по ключу "auth" (`$this->config->auth`).

```php
<?php
[
    'auth' => [
        'defaults' => [
            'guard' => 'web'
        ],
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'adapter' => 'model',
                'model'  => App\Models\User::class,
            ]
        ]
    ],
];
```

Т.е. по дефолту приложение будет использовать `guard = web`. В свою очередь Охранник web основан на драйвере `session` и использует поставщика пользователей `users`, которые извлекаются из Адаптера `model` - `App\Models\Users`.
Данный конфигурационный файл позволяет создавать различные комбинации охранников и поставщиков, разделяя доступы в вашем приложений.

### Пример конфигурационного файла для использования Токена

```php
<?php
[
    'auth' => [
        'defaults' => [
            'guard' => 'api'
        ],
        'guards' => [
            'api' => [
                'driver' => 'token',
                'provider' => 'users',
                'inputKey' => 'auth_token', //опционально 
                'storageKey' => 'auth_token', //опционально
            ],
        ],
        'providers' => [
            'users' => [
                'adapter' => 'model',
                'model'  => App\Models\User::class,
            ]
        ]
    ],
];
```
По умолчанию имя параметра в запросе и поле в базе данных таблицы `users` равно `auth_token`, например в GET запросе:
```shell
//GET
https://yourapidomain/api/v2/users?auth_token=fGaYgdGPSfEgT41r3F4fg33
```
или POST:
```shell
//POST
//params POST request
[
  "auth_token": "fGaYgdGPSfEgT41r3F4fg33"
]

https://yourapidomain/api/v2/users
```

или заголовок `Authorization`:

```shell
Authorization: Bearer fGaYgdGPSfEgT41r3F4fg33

https://yourapidomain/api/v2/users
```

Имя параметра и поле в таблицце БД можно изменить с помощью конфига охранника, задав такие параметры как:

```php
[
    ... 
    'inputKey'   => 'token', //имя параметра с токеном
    'storageKey' => 'token', //имя поля в таблице бд
    ...
]
```

> Помните, что каждый ваш запрос к приложению, должен сопровождаться параметром `auth_token` с токеном доступа.

## Auth Manager

С помощью `Sinbadxiii\PhalconAuth\Manager` можно создать сервис провайдер аутентификации:

```php
$di->setShared('auth', function () {
    return new \Sinbadxiii\PhalconAuth\Manager();
});
```

`Sinbadxiii\PhalconAuth\Manager` по умолчанию использует конфигурацию из `$this->config->auth`, если вы хотите использовать другую конфигурацию, можно передать в качестве первого аргумента:

```php
$di->setShared('auth', function () {
    $authConfig = $this->getConfig()->get("auth_other");
    
    return new \Sinbadxiii\PhalconAuth\Manager($authConfig);
});
```

В качестве второго аргумента можно передать отличный от глобального сервис провайдера `$this->security`.
## Guards

На данный момент существует два вида Охранников, которые покроют 90% типовых задач аутентификации веб-приложений.
Это `Sinbadxiii\PhalconAuth\Guard\Session` и `Sinbadxiii\PhalconAuth\Guard\Token`. Указывая в качестве driver один из этих guards вы выбираете, что будете использовать в своем приложении, аутентификацию на основе сессий или токена,
`'driver' => 'session'` или `'driver' => 'token'`.

Предположительно Сессии вы будете использовать в обычных веб-приложениях, а Токен охранника в микро приложениях в качестве api сервисов. Но ничего вам не мешает применять или комбинировать охранников в не стандартных приложениях.

Реализуя интерфейс `Sinbadxiii\PhalconAuth\Guard\GuardInterface` вы можете создать своего Guard, добавить его в настройки и расширить список охранников `Sinbadxiii\PhalconAuth\Manager`, например:
```php
$di->setShared('auth', function () {
    $auth = new \Sinbadxiii\PhalconAuth\Manager();
    
    $configGuard = $this->getConfig()->auth->guard->web;
    
    $auth->addGuard('jwt', function($name, $provider, $config) use ($auth) {
        return new JWTGuard($name, $auth->getAdapterProvider($configGuard->provider), $configGuard);
    });
    
    return $auth;
});
```
```php
<?php
...
'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
]
...
```

## Access

С помощью Доступов (Access) вы можете разрешать или запрещать доступ к тем или иным областям приложения, например в контроллер профиля пользователя мы разрешаем доступ только аутентифицрованным пользователям:
```php 
<?php

declare(strict_types=1);

namespace App\Controllers;

class ProfileController extends ControllerBase
{
    public function onConstruct()
    {
        $this->auth->access("auth");
    }

    public function indexAction()
    {
    }
}
```

А к контроллеру регистрации, например, нужен доступ только неаутентифицированным пользователям - гостям:

```php 
<?php

declare(strict_types=1);

namespace App\Controllers;

class RegisterController extends ControllerBase
{
    public function onConstruct()
    {
        $this->auth->access("guest");
    }

    public function indexAction()
    {
    }
}
```

Из коробки есть два основных вида доступа - аутентифицированный и гостевой:

- `Sinbadxiii\PhalconAuth\Access\Auth`
- `Sinbadxiii\PhalconAuth\Access\Guest`

Если доступ удовлетворяет условию в методе `allowIf`, то дается разрешение на дальнейшее использование контроллера, например в дефолтном `auth` условием является:

```php 
class Auth extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($this->auth->check()) {
            return true;
        }

        return false;
    }
}
```

`$this->auth->check()` - проверка на аутентификацию пользователя, т.е. чтобы получить доступ к `$this->auth->access('auth')` нужно быть аутентифицированным, а вот условие у `$this->auth->access('guest')` прямо противоположно:

```php 
<?php

namespace Sinbadxiii\PhalconAuth\Access;

/**
 * Class Guest
 * @package Sinbadxiii\PhalconAuth\Access
 */
class Guest extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($this->auth->guest()) {
            return true;
        }

        return false;
    }
}
```

В случае если метод `allowedIf()` вернет `true`, то пользователь сможет идти дальше, если же результат будет равен `false`, то сработает метод неудачи `redirectTo()`, т.к. у каждого приложение пути роутов редиректа могут быть разными, то вам следует создать свои классы Access `auth` и `guest`, наследовав от дефолтных классов и переопределив методы `redirectTo()`:
```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\Auth as AuthAccess;;

class Auth extends AuthAccess
{
    public function redirectTo()
    {
        if (isset($this->response)) {
            return $this->response->redirect("/login")->send();
        }
    }
}
```
и
```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\Guest as GuestAccess;

class Guest extends GuestAccess
{
    public function redirectTo()
    {
        if (isset($this->response)) {
            return $this->response->redirect("/profile")->send();
        }
    }
}
```

Чтобы создать свой Access, можно имплементировать интерфейс `Sinbadxiii\PhalconAuth\Access\AccessInterface`:

```php 
<?php

namespace Sinbadxiii\PhalconAuth\Access;

/**
 * Interface for Sinbadxiii\PhalconAuth\Access
 */
interface AccessInterface
{
    public function except(...$actions): void;
    public function getExceptActions(): array;
    public function only(...$actions): void;
    public function getOnlyActions(): array;
    public function isAllowed(): bool;
    public function redirectTo();
    public function allowedIf(): bool;
}
```
либо просто наследовав абстрактный класс `Sinbadxiii\PhalconAuth\Access\AccessAbstract` для более быстрого и гибкого использования кастомных доступов, например, давайте создадим доступ для пользователей, имеющих роль админа:
```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\AccessAbstract;

class Admin extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($user = $this->auth->user() and $user->getRole() === "admin") {
            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function redirectTo()
    {
        if (isset($this->response)) {
            return $this->response->redirect("/admin-login")->send();
        }
    }
}
```
или пример проверки доступа для Http Basic Auth:

```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\AccessAbstract;
use Sinbadxiii\PhalconAuth\Exception;

class AuthWithBasic extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($this->auth->basic("email")) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function redirectTo()
    {
        throw new Exception("Basic: Invalid credentials.");
    }
}
```
Метод `except()` позволяет добавить список экшнов, который нужно будет исключить:
```php 
<?php

declare(strict_types=1);

namespace App\Controllers;

class OrdersController extends ControllerBase
{
    public function onConstruct()
    {
        $this->auth->access("auth")->except("statistic", "reports");
    }

    public function indexAction()
    {
    }
    
    public function statisticAction()
    {
    }
    
    public function reportsAction()
    {
    }
}
```
означает, что к контроллеру `OrdersController` предоставляется только пользователям с доступом`auth`, кроме экшнов `statisticAction`, `reportsAction`.

Метод `only()` имеет противоположную функцию, только перечисленным в нем экшнам будет запрашиваться требуемый доступ.

## Регистрация доступов

Доступы должны быть зарегистрированы в системе аутентификации, если этого не сделать, то будет выдаваться ошибка, типа:
`Access with 'auth' name is not included in the access list`. 

Чтобы зарегистрировать доступы в системе, нам необходимо будет создать некоторое промежуточное программное обеспечение, подтипа middleware и прикрепить его к `dispatcher` приложения.

Минимальный вид вашего класса `App\Security\Authenticate`, будет таков: 
```php 
<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Access\Auth;
use App\Security\Access\Guest;
use Sinbadxiii\PhalconAuth\Access\Authenticate as AuthMiddleware;

/**
 * Class Authenticate
 * @package App\Security
 */
class Authenticate extends AuthMiddleware
{
    protected array $accessList = [
        'auth'   => Auth::class,
        'guest'  => Guest::class
    ];
}
```

А затем мы прикрепим его к сервиc-провайдеру `dispatcher` нашего приложения:

```php 
$di->setShared('dispatcher', function () use ($di) {

    $dispatcher = new Phalcon\Mvc\Dispatcher();
    $eventsManager = $di->getShared('eventsManager');
    $eventsManager->attach('dispatch', new App\Security\Authenticate());
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});
```

Свойство `$accessList` позволяет вам быстро добавлять новые уровни доступа в вашем приложении, например добавив новый доступ `admin`, нам достаточно добавить его в список `$accessList`:

```php 
<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Access\Auth;
use App\Security\Access\Admin;
use App\Security\Access\Guest;
use Sinbadxiii\PhalconAuth\Access\Authenticate as AuthMiddleware;

/**
 * Class Authenticate
 * @package App\Security
 */
class Authenticate extends AuthMiddleware
{
    protected array $accessList = [
        'auth'   => Auth::class,
        'guest'  => Guest::class,
        'admin'  => Admin::class,
    ];
}
```
Так же список доступов можно зарегистрировать непосредственно в Manager при создании сервис провайдера с помощью метода `setAccessList()`:
```php 

$authManager =  new Sinbadxiii\PhalconAuth\Manager();

$authManager->setAccessList(
    [
        'auth'   => App\Security\Access\Auth::class,
        'guest'  => App\Security\Access\Guest::class,
        'admin'  => App\Security\Access\Admin::class,
    ];
);
    
return $authManager;
```

## Поставщики (Providers)

Как уже было сказано ранее поставщики определяют какие сущности будут являться пользователями, например `users` или `contacts`, все зависит от контекста вашего приложения, если взять стандартный конфигурационный файл, то можно увидеть, что поставщиками тут являются `users`:

```php 
    'auth' => [
        'defaults' => [
            'guard' => 'web'
        ],

        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'adapter' => 'model',
                'model'  => App\Models\User::class
            ],
        ]
    ],
```
## Адаптер поставщика `model`

Поставщиками у нас являются `users`, и в качестве адаптера используется стандартная `model` - `App\Models\User::class`:

```php 
<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class User extends Model
{
    public $id;
    public $username;
    public $name;
    public $email;
    public $password;
    public $published;
    public $created_at;
    public $updated_at;

    public function initialize()
    {
        $this->setSource("users");
    }
}
```

если сейчас попробовать использовать эту модель, то выдается ошибка:

`PHP Fatal error:  Uncaught TypeError: Sinbadxiii\PhalconAuth\Adapter\Model::validateCredentials(): Argument #1 ($user) must be of type Sinbadxiii\PhalconAuth\AuthenticatableInterface`.

Т.е. модель `User` надо имплементировать от `Sinbadxiii\PhalconAuth\AuthenticatableInterface`, а если хочется использовать возможности функции `RememberMe` (Запомнить меня), то так же надо будет наследовать интерфейс `Sinbadxiii\PhalconAuth\RememberingInterface`:
В конечном счете, ваша модель должна иметь вид следующего класса:

```php 
<?php

namespace App\Models;

use Phalcon\Di\Di;
use Phalcon\Encryption\Security\Random;
use Phalcon\Mvc\Model;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

class User extends Model implements AuthenticatableInterface, RememberingInterface
{
    public $id;
    public $username;
    public $name;
    public $email;
    public $password;
    public $published;
    public $created_at;
    public $updated_at;

    public function initialize()
    {
        $this->setSource("users");

        $this->hasOne(
            'id',
            RememberToken::class,
            'user_id',
            [
                'alias' => "remember_token"
            ]
        );
    }

    public function setPassword(string $password)
    {
        $this->password = Di::getDefault()->getShared("security")->hash($password);
        return $this;
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

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

    public function createRememberToken(): RememberTokenInterface
    {
        $random = new Random();

        $token = $random->base64(60);

        $rememberToken = new RememberToken();
        $rememberToken->token = $token;
        $rememberToken->user_agent = Di::getDefault()->get('request')->getUserAgent();
        $rememberToken->ip = Di::getDefault()->get('request')->getClientAddress();

        $this->setRememberToken($rememberToken);
        $this->save();

        return $rememberToken;
    }
}
```

Интерфейс `Sinbadxiii\PhalconAuth\AuthenticatableInterface` имеет следущий вид:

```php 
<?php

namespace Sinbadxiii\PhalconAuth;

interface AuthenticatableInterface
{
    public function getAuthIdentifier();
    public function getAuthPassword();
}
```

а реализация `Sinbadxiii\PhalconAuth\RememberingInterface`:

```php 
<?php

namespace Sinbadxiii\PhalconAuth;

interface RememberingInterface
{
    public function getRememberToken(): ?RememberTokenInterface;
    public function createRememberToken(): RememberTokenInterface;
}
```

## Адаптер поставщика `stream`

Если взять в качестве адаптера поставщиков `users` не `model`, а файл `stream`:

```php 
    'auth' => [
        'defaults' => [
            'guard' => 'web'
        ],

        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'adapter' => 'stream',
                'src'  => __DIR__ . "/users.json",
            ],
        ]
    ],
```

то используя параметр `src` мы можем задать источник файла `users.json`, который имеет вид:

```json 
[
  {"name":"admin", "username":"admin", "password": "admin","email": "admin@admin.ru"},
  {"name":"user", "username":"user", "password": "user","email": "user@user.ru"}
]
```

Возвращаемый пользователь в виде модели `Sinbadxiii\PhalconAuth\Adapter\User` будет реализовывать интерфейс `Sinbadxiii\PhalconAuth\AuthenticatableInterface`, но не может использовать функцию `RememberMe` (Запомнить меня), т.к.
не имплементирует интерфейс `Sinbadxiii\PhalconAuth\RememberingInterface` ввиду отсутствия возможности сохранить токен сессии. 

Это стоит учитывать при разработке приложения.

## Адаптер поставщика `memory`

Если взять в качестве адаптера поставщиков `memory`:

```php 
    'auth' => [
        'defaults' => [
            'guard' => 'web'
        ],

        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'adapter' => 'memory',
                'data'   => [
                    ["id" => 0, "username" =>"admin", "name" => "admin", 'password' => 'admin', "email" => "admin@admin.ru"],
                    ["id" => 1, "username" =>"user", "name" => "user", 'password' => 'user', "email" => "user@user.ru"],
                ],
            ],
        ]
    ],
```

то используя параметр `data` мы можем задать массив данных с пользователями, который имеет вид:

```php 
[
    ["id" => 0, "username" =>"admin", "name" => "admin", 'password' => 'admin', "email" => "admin@admin.ru"],
    ["id" => 1, "username" => "user", "name" => "user", 'password' => 'user', "email" => "user@user.ru"],
]
```

> Не рекомендуется использовать адаптеры `stream` и `memory` в реальных приложениях из-за их функциональной ограниченности и сложности управления пользователями. Это может быть полезно в прототипах приложений и для ограниченных приложений, которые не хранят пользователей в базах данных.

## Создание своего адаптера поставщика

Интерйес поставщика `Sinbadxiii\PhalconAuth\Adapter\AdapterInterface;` имеет вид:

```php 
<?php

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface AdapterInterface
{
    public function retrieveByCredentials(array $credentials);
    public function retrieveById($id);
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool;
}
```

Реализовав все методы интерфейса, вы сможете расширить список адаптеров с помощью метода `addProviderAdapter`, например:

```php 
$di->setShared("auth", function () {
    $authManager =  new Phalcon\Auth\Manager();

    $security = $this->getSecurity();
    $configProvider = $this->getConfig()->auth->providers->users;

    $authManager->addProviderAdapter("mongo", function($security, $configProvider) {
        return new App\Security\Adapter\Mongo($security, $configProvider);
    } );

    return $authManager;
});
```

Так же для создания функционала "Запомнить меня" нужна реализация интерфейса `Sinbadxiii\PhalconAuth\Adapter\AdapterWithRememberTokenInterface`:

```php 
<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

interface AdapterWithRememberTokenInterface
{
    public function retrieveByToken($identifier, $token, $user_agent);
    public function createRememberToken(RememberingInterface $user): RememberTokenInterface;
}
```

## Методы

### Проверка аутентификации текущего пользователя

Чтобы определить, аутентифицирован ли пользователь, выполняющий входящий HTTP-запрос, вы можете использовать метод `check()`. Этот метод вернет true, если пользователь аутентифицирован:
```php
$this->auth->check(); 
//check authentication
```

например, вы можете проверить на странице формы входа, что если пользователь вошел в систему, то не показывать ему форму ввода:

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

### Получение аутентифицированного пользователя

При обработке входящего запроса вы можете получить доступ к аутентифицированному пользователю с помощью метода `user()`. Результатом будет провайдер, указанный в конфигурации `config->auth`, в соответствии с интерфейсом `Sinbadxiii\PhalconAuth\AuthenticatableInterface`.

Вы также можете запросить идентификатор пользователя (ID) с помощью метода `id()`:

```php 
$this->auth->user(); //get the user

$this->auth->id(); //get user id
```

### Попытка аутентификации

Метод `attempt()` используется для обработки попыток аутентификации из формы входа в ваше приложение:
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

Метод `attempt()` принимает в качестве первого аргумента массив пар ключ/значение. Значения в массиве будут использоваться для поиска пользователя в таблице базы данных пользователей. Итак, в приведенном выше примере пользователь будет получен по значению столбца имени пользователя. Если пользователь найден, хешированный пароль, хранящийся в базе данных, будет сравниваться со значением пароля, переданным методу. Вы не должны хешировать значение входящего запроса пароля, так как пароль уже автоматически хэшируется, чтобы сравнить его с хешированным паролем в базе данных. Аутентифицированный сеанс будет запущен для пользователя, если хешированные пароли совпадают.

Помните, что пользователи из вашей базы данных будут запрашиваться на основе конфигурации «поставщика».

Метод `attempt()` вернет `true`, если аутентификация прошла успешно. В противном случае будет возвращено `Sfalse`.

### Указание дополнительных учетных данных

Вы также можете добавить дополнительные учетные данные запроса в дополнение к электронной почте/имени пользователя и паролю. Для этого просто добавьте условия запроса в массив, переданный методу `attempt()`. Например, мы можем проверить, помечен ли пользователь как «is_published»:

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

### "Запомнить меня"

Если вы хотите обеспечить функциональность «запомнить меня» в своем приложении, вы можете передать логическое значение в качестве второго аргумента метода попытки.

Если это значение равно `true`, пользователь будет аутентифицироваться на неопределенный срок или до тех пор, пока пользователь не выйдет из системы вручную с помощью `logout()`. Таблица `users_remember_tokens` содержит столбец строки токена, который будет использоваться для хранения токена «запомнить меня»:
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
Используйте метод `viaRemember()`, чтобы проверить, аутентифицирован ли пользователь с помощью файла cookie «запомнить меня»:
```php
//use method viaRemember to check the user was authenticated using the remember me cookie
$this->auth->viaRemember();
```

### Аутентифицировать пользовательский экземпляр

Если вам нужно установить существующий пользовательский экземпляр в качестве текущего аутентифицированного пользователя, вы можете передать пользовательский экземпляр методу `login()`. Данный пользовательский экземпляр должен быть реализацией `Sinbadxiii\PhalconAuth\AuthenticatableInterface`.

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

Метод `$this->auth->basic("email")` позволит создать свой Access для использования доступа с помощью Auth Basic.

Аргумент `email` указывает на то, что поиск пользователя будет осуществляться по полям email и password. Указав другое поле, например `username`, поиск будет осуществляться по паре `username` и `password`:

```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\AccessAbstract;
use Sinbadxiii\PhalconAuth\Exception;

class AuthWithBasic extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($this->auth->basic("email")) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function redirectTo()
    {
        throw new Exception("Basic: Invalid credentials.");
    }
}
```

После запроса, в сессию запишется пользователь, и последующие запросы, могут уже не содержать пользовательские данные в заголовке `Authorization`, до тех пор пока сессия не "стухнет".

### Basic HTTP-аутентификация без сохранения состояния

Вы можете использовать базовую аутентификацию HTTP без сохранения пользователя в сессии. Это в первую очередь полезно, если вы решите использовать HTTP-аутентификацию для аутентификации запросов к API вашего приложения. Для этого можно создать Access, который вызывает метод `onceBasic()`, например:

```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\AccessAbstract;
use Sinbadxiii\PhalconAuth\Exception;

class AuthWithBasic extends AccessAbstract
{
    /**
     * @return bool
     */
    public function allowedIf(): bool
    {
        if ($this->auth->onceBasic("email")) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function redirectTo()
    {
        throw new Exception("Basic: Invalid credentials.");
    }
}
```

После запроса, ни куки, ни сессия не будут содержать данные о пользователе, и следущий запрос так же должен содержать пользовательские данные заголовка `Authorization`, иначе будет вызвано исключение `Sinbadxiii\PhalconAuth\Exceptions`

### License
The MIT License (MIT). Please see [License File](https://github.com/sinbadxiii/phalcon-auth/blob/master/LICENSE) for more information.