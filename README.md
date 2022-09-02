# Phalcon Auth

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth/phalcon-auth-logo.png?raw=true)

You can see an example of an application with authentication here [sinbadxiii/phalcon-auth-example](https://github.com/sinbadxiii/phalcon-auth-example)

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></img></a>
<a href="https://packagist.org/packages/sinbadxiii/phalcon-auth"><img src="https://img.shields.io/packagist/dt/sinbadxiii/phalcon-auth?style=flat-square" alt="Packagist Downloads"></img></a>
<a href="https://github.com/sinbadxiii/phalcon-auth/releases"><img src="https://img.shields.io/github/release/sinbadxiii/phalcon-auth?style=flat-square" alt="Latest Version"></img></a>
</p>

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

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth/auth-scheme.webp?raw=true)

Phalcon Auth позволит вам создать систему аутентификации в вашем веб-приложении.

Система аутентификации имеет такие понятия как «Охранники» (Guard) и «Поставщики» (Provider), охранники определяют, как пользователи будут аутентифицироваться, например, используя стандартные Хранилища Сессии и файлов куки.

Провайдеры определяют, какие данные будут браться в качестве пользователей, и так же откуда будут извлекаться эти пользователи. Откуда будут извлекаться данные пользователей определяют Адаптеры (Adapter). Обычно это `Phalcon\Mvc\Model` и построитель запросов к базе данных.

Кроме того есть другие варианты адаптеров: файл или массив с данными. Можно создать свой адаптер, реализуя интерфейс адаптера. Об этом поговорим чуть позже. 

> Guards и Providers не следует путать с «roles» и «permissions» [ACL](https://docs.phalcon.io/4.0/en/acl). Auth и ACL  следует использовать вместе, если требуется более точная надстройка доступа к узлам приложения. Например использовать роль `manager` со специфическими правами.

## Быстрый старт

Полностью пример готового приложения с аутентификацией доступен по адресу [sinbadxiii/phalcon-auth-example](https://github.com/sinbadxiii/phalcon-auth-example). Это типовой проект на Phalcon, который можно использовать как старт нового приложения, либо же просто ознакомиться с возможностями аутентификации на примере данного приложения.  

## Логика работы

Общий принцип работы аутентификации заключается в том, что пользователь вводит свое имя пользователя и пароль через форму входа. Если эти учетные данные верны, приложение сохранит информацию об аутентифицированном пользователе в сессии пользователя и будет считать пользователя "аутентифицированным". В случае использования "Запомнить меня" может быть создан файл cookie, который содержит идентификатор сессии, чтобы последующие запросы к приложению могли быть связаны с нужным пользователем. После получения идентификатора сессии из файла cookie приложение извлечет данные пользователя.

Возьмем другой случай, когда удаленному сервису необходимо пройти аутентификацию для доступа к API, обычно файлы cookie не используются для аутентификации, поскольку веб-браузер отсутствует. Вместо этого удаленная служба отправляет токен API при каждом запросе. Приложение может проверить входящий токен по таблице действительных токенов API и "аутентифицировать" запрос как выполненный пользователем, связанным с этим токеном API.

## Подготовка базы данных
 
Для использования данных из бд, понадобится создать таблицу `users`.

Если необходимо будет использовать функцию "Запомнить меня" - `RememberMe`, которая позволяет хранить сеанс аутентификации пользователя длительное время, то так же понадобится таблица `users_remember_tokens`, ну и соответственно ее модель в виде `App\Models\RememberToken`.

Для быстрого создания таблиц вы можете импортировать файлы из папки `db/users.sql`, `db/users_remember_tokens.sql`, а так же `db/create_auth_token_users.sql`, если будете использовать в качестве Guard - Token, которому необходимо поле `auth_token` для корректной работы.

## Managers

При создании аутентификации вы можете воспользоваться одним из двух менеджеров: `Sinbadxiii\PhalconAuth\Manager` или `Sinbadxiii\PhalconAuth\ManagerFactory`.

## Manager

Если вы строго придерживаетесь философии фреймворка Phalcon и хотите вручную настроить все компоненты аутентификации, то вам понадобится класс `Sinbadxiii\PhalconAuth\Manager` - с помощью данного менеджера можно настроить охранника, адаптер поставщиков и распределить доступы пользователям.  

```php
use Sinbadxiii\PhalconAuth\Manager;
use App\Models\User;
use Sinbadxiii\PhalconAuth\Adapter\Model;
use Sinbadxiii\PhalconAuth\Guard\Session;

$auth = new Manager();

$configAdapter = [
    'model' => User::class,
];

$adapter = new Model($this->getSecurity(), $configAdapter);
$guard   = new Session(
    $adapter,
    $this->getSession(),
    $this->getCookies(),
    $this->getRequest(),
    $this->getEventsManager()
);

$auth->addGuard("web", $guard, true);

return $auth;
```

В результате получился менеджер, который будет искать пользователей через модель `User` в таблице базе данных `users`.
Результат аутентификации будет храниться в сессии, и куках, если выбрать "Запомнить меня".
В качестве других аргументов нужно передать сервис провайдеры `$this->security`, `$this->session`, `$this->cookies`, `$this->request`, `$this->eventsManager`, которые будут необходимы при дальнейшем использовании охранника и адаптера поставщиков.

- public <b>addGuard</b>(string $nameGuard, GuardInterface $guard, bool $isDefault = false) - добавить охранника
- public <b>guard</b>(?string $name = null) - получить конкретного охранника или по заданного по дефолту 
- public <b>setDefaultGuard</b>(GuardInterface $guard) - задать охранника по дефолту 
- public <b>getDefaultGuard</b>() - получить охранника по дефолту 
- public <b>acces</b>s(string $accessName) - назначить контроллеру определенный доступ 
- public <b>getAccess</b>(string $accessName) - назначить требуемый доступ 
- public <b>setAccess</b>(AccessInterface $access) - получить требуемый доступ 
- public <b>setAccessList</b>(array $accessList) - зарегистрировать список доступов 
- public <b>addAccessList</b>(array $accessList) - добавить список доступов 
- public <b>except</b>(...$actions) - исключенные экшны из проверки доступа 
- public <b>only</b>(...$actions) - обязательные экшны для проверки доступа 
- public <b>__call</b>() - magic __call

## Guards

На данный момент существует два вида Охранников, которые покроют 90% типовых задач создания аутентификации веб-приложений.
Это `Sinbadxiii\PhalconAuth\Guard\Session` и `Sinbadxiii\PhalconAuth\Guard\Token`, указывая одного из этих охранников вы выбираете, что будете использовать в своем приложении, аутентификацию на основе сессий или токена.

Предположительно Сессии вы будете использовать в веб-приложениях после логина в личный кабинет,а Токен, например, в микро приложениях в качестве api сервисов. Но ничего вам не мешает применять или комбинировать охранников в нестандартных приложениях.

## Session Guard

```php
use Sinbadxiii\PhalconAuth\Manager;
use App\Models\User;
use Sinbadxiii\PhalconAuth\Adapter\Model;
use Sinbadxiii\PhalconAuth\Guard\Session;

$auth = new Manager();

$configAdapter = [
    'model' => User::class,
];

$adapter = new Model($this->getSecurity(), $configAdapter);
$guard   = new Session(
    $adapter,
    $this->getSession(),
    $this->getCookies(),
    $this->getRequest(),
    $this->getEventsManager()
);

$auth->addGuard("web", $guard, true);

return $auth;
```

- public function <b>__construct</b>(AdapterInterface $adapter, SessionManagerInterface $session,
  Cookies $cookies, Request $request, EventsManagerInterface $eventsManager)
- public function <b>attempt</b>(array $credentials = [], $remember = false) - попытка аутентификации
- public function <b>user</b>() - получить аутентифицированного пользователя
- public function <b>validate</b>(array $credentials = []) - валидация входных данных
- public function <b>getName</b>() - получение имени сессии
- public function <b>getRememberName</b>() - имя куки при запомнить меня
- public function <b>login</b>(AuthenticatableInterface $user, bool $remember = false) - логин экземпляра пользователя
- public function <b>loginById</b>($id, bool $remember = false) - логин по Id пользователя
- public function <b>once</b>(array $credentials = []) - логин без сохранения пользователя в сессию
- public function <b>logout</b>() - выход 
- public function <b>getLastUserAttempted</b>() - получение последнего попытавшегося залогиниться пользователя
- public function <b>viaRemember</b>() - проверка что пользователь был вытащен из Запомнить меня
- public function <b>getUser</b>() - получить пользователя
- public function <b>setRequest</b>(Request $request)
- public function <b>setSession</b>(SessionManagerInterface $session)
- public function <b>setCookies</b>(Cookies $cookies)
- public function <b>getAdapter</b>() - получить адаптер поставщика
- public function <b>setAdapter</b>(AdapterInterface $adapter) - назначить адаптера поставшика

Basic

- public function <b>basic</b>(string $field = 'email', array $extraConditions = []) - аутентификация через Basic Auth
- public function <b>onceBasic</b>(string $field = 'email', array $extraConditions = []) - аутентификация через Basic Auth без сохранения в сессию

## Token Guard

Чтобы воспользоваться `Sinbadxiii\PhalconAuth\Guard\Token`, необходимо в качестве второго аргумента передать конфиг с названиями имя параметра запроса и поля в хранилище данных пользователей, например, поле таблицы `users` в бд:

```php
[
    ... 
    'inputKey'   => 'auth_token', //имя параметра с токеном
    'storageKey' => 'auth_token', //имя поля в хранилище пользователей
    ...
]
```
```php
use Sinbadxiii\PhalconAuth\Manager;
use App\Models\User;
use Sinbadxiii\PhalconAuth\Adapter\Model;
use Sinbadxiii\PhalconAuth\Guard\Token;

$auth = new Manager();

$configAdapter = [
    'model' => User::class,
];

$configGuard = [
    'inputKey'   => 'auth_token',
    'storageKey' => 'auth_token',
];

$adapter = new Model($this->getSecurity(), $configAdapter);
$guard   = new Token(
    $adapter,
    $configGuard
    $this->getRequest()
);

$auth->addGuard("api", $guard, true);

return $auth;
```
Соответствено GET запрос должен будет иметь вид: 

```shell
//GET
https://yourapidomain/api/v2/users?auth_token=fGaYgdGPSfEgT41r3F4fg33
```
POST запрос:
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

> Помните, что каждый ваш запрос к приложению, должен сопровождаться параметром `auth_token` с токеном доступа.

- public function <b>__construct</b>(AdapterInterface $adapter, array $config, Request $request)
- public function <b>user</b>() - аутентифицированный пользователь
- public function <b>validate</b>(array $credentials = []) - валидация
- public function <b>getTokenForRequest</b>() - поулчить токен из запросов (GET, POST, Headers)
- public function <b>setRequest</b>(Request $request)
- public function <b>getRequest</b>()
- public function <b>getAdapter</b>()
- public function <b>setAdapter</b>(AdapterInterface $adapter)

## Создание своего Охранника

```php 
<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Guard;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface GuardInterface
{
    public function check(): bool;
    public function user();
    public function setUser(AuthenticatableInterface $user);
    public function id();
    public function guest(): bool;
    public function validate(array $credentials = []): bool;
}
```

Реализуя интерфейс `Sinbadxiii\PhalconAuth\Guard\GuardInterface` вы можете создать своего Guard.

## Access

С помощью Доступов (Access) вы можете задавать и проверять требуемый доступ к тем или иным областям приложения, например в контроллер профиля пользователя разрешен доступ только аутентифицированным пользователям.

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
В то время как к контроллеру регистрации, например, нужен доступ только неаутентифицированным пользователям - гостям:

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
Задается доступ в конструкторе контроллера `onConstruct()`.

Из коробки есть два основных вида доступа - аутентифицированный и гостевой:

- `Sinbadxiii\PhalconAuth\Access\Auth`
- `Sinbadxiii\PhalconAuth\Access\Guest`

Если доступ удовлетворяет условию в методе `allowIf`, то дается разрешение на дальнейшее использование контроллера, например в дефолтном `auth` условием является:

```php 
class Auth extends AbstractAccess
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
class Guest extends AbstractAccess
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
В случае если метод `allowedIf()` вернет `true`, то пользователь сможет идти дальше, если же результат будет равен `false`, то сработает метод неудачи `redirectTo()`, и приложение перенаправит пользователя, т.к. у каждого приложение логика перенаправлений может быть разная, то вам следует создать свои классы Access `auth` и `guest`, наследовав от дефолтных классов и переопределить метод `redirectTo()`:
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
    public function setExceptActions(...$actions): void;
    public function setOnlyActions(...$actions): void;
    public function isAllowed(): bool;
    public function redirectTo();
    public function allowedIf(): bool;
}
```
либо просто наследовав абстрактный класс `Sinbadxiii\PhalconAuth\Access\AbstractAccess` для более быстрого и гибкого использования кастомных доступов, например, давайте создадим доступ для пользователей, имеющих роль админа:
```php 
<?php

namespace App\Security\Access;

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;

class Admin extends AbstractAccess
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

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;
use Sinbadxiii\PhalconAuth\Exception;

class AuthWithBasic extends AbstractAccess
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

## Регистрация доступов

Доступы должны быть зарегистрированы в системе аутентификации, если этого не сделать, то при запросе доступа `$this->auth->access("auth")` будет выдаваться ошибка, типа:
`Access with 'auth' name is not included in the access list`. 

Чтобы зарегистрировать доступы в системе, необходимо создать некоторое промежуточное программное обеспечение, подтипа middleware и прикрепить его к `dispatcher` приложения.

Минимальный вид класса `App\Security\Authenticate` будет таков: 
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

А затем необходимо прикрепить его к сервиc-провайдеру `dispatcher`:

```php 
$di->setShared('dispatcher', function () use ($di) {

    $dispatcher = new Phalcon\Mvc\Dispatcher();
    $eventsManager = $di->getShared('eventsManager');
    $eventsManager->attach('dispatch', new App\Security\Authenticate());
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});
```
Свойство `$accessList` позволяет быстро добавлять новые уровни доступа в приложении, например, чтобы добавить новый доступ `admin`, достаточно создать класс с условием и добавить его в список `$accessList`:
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

## Поставщики (Providers) и Адаптеры (Adapters) 

Как уже было сказано ранее поставщики определяют какие сущности будут являться пользователями, например `users` или `contacts`, все зависит от контекста вашего приложения.

В настоящий момент существуют три вида адаптера:

- `Sinbadxiii\PhalconAuth\Adapter\Model`
- `Sinbadxiii\PhalconAuth\Adapter\Stream`
- `Sinbadxiii\PhalconAuth\Adapter\Memory`

Модель, файл и массив с данными в приложении.
Все адаптеры наследуются от абстрактного класса `Sinbadxiii\PhalconAuth\Adapter\AbstractAdapter`, который имеет:

- public <b>setModel</b>(AuthenticatableInterface $model)` - назначить модель поставщика
- public <b>getModel</b>()` - получить модель поставщика
- public <b>setConfig</b>(array $config)` - установить конфиг
- public <b>getConfig</b>()` - получить конфиг адаптера

## Адаптер поставщика `Model`

Для использования адаптера `Model` нам понадобится модель пользователя, например `App\Models\User::class` вида:

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

Чтобы при использовании в нашем приложении, не выдавалась ошибка:

`PHP Fatal error:  Uncaught TypeError: Sinbadxiii\PhalconAuth\Adapter\Model::validateCredentials(): Argument #1 ($user) must be of type Sinbadxiii\PhalconAuth\AuthenticatableInterface`.

Т.е. модель `User` надо имплементировать от `Sinbadxiii\PhalconAuth\AuthenticatableInterface`, а если хочется использовать возможности функции `RememberMe` (Запомнить меня), то так же надо будет наследовать интерфейс `Sinbadxiii\PhalconAuth\RememberingInterface`:
В конечном счете, ваша модель должна иметь класс следующего вида:

```php 
<?php

namespace App\Models;

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
        $this->password = $this->getDI()->get("security")->hash($password);
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
        $rememberToken->user_agent = $this->getDI()->get("request")->getUserAgent();
        $rememberToken->ip =  $this->getDI()->get("request")->getClientAddress();

        $this->setRememberToken($rememberToken);
        $this->save();

        return $rememberToken;
    }
}
```

Модель `App\Models\RememberToken` будет иметь вид:

```php 
<?php

declare(strict_types=1);

namespace App\Models;

use Phalcon\Mvc\Model;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

class RememberToken extends Model implements  RememberTokenInterface
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $user_agent;

    /**
     * @var integer
     */
    public $created_at;

    /**
     * @var integer
     */
    public $updated_at;

    /**
     * @var integer
     */
    public $expired_at;

    public function initialize()
    {
        $this->setSource("users_remember_tokens");
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserAgent(): string
    {
        return $this->user_agent;
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

а реализация "Запомнить меня" - `Sinbadxiii\PhalconAuth\RememberingInterface`:

```php 
<?php

namespace Sinbadxiii\PhalconAuth;

interface RememberingInterface
{
    public function getRememberToken(): ?RememberTokenInterface;
    public function createRememberToken(): RememberTokenInterface;
}
```

Теперь можно использовать модель при создании менеджера:

```php 
    use Sinbadxiii\PhalconAuth\Adapter\Model;
    use Sinbadxiii\PhalconAuth\Guard\Session;
    use Sinbadxiii\PhalconAuth\Manager;

    $security = $this->getSecurity();

    $adapter = new Model($security);
    $adapter->setModel(App\Models\User::class);
    $guard   = new Session(
        $adapter,
        $this->getSession(),
        $this->getCookies(),
        $this->getRequest(),
        $this->getEventsManager()
    );


    $manager = new Manager();
    $manager->addGuard("web", $guard);
    
    $manager->setDefaultGuard($guard);

    return $manager;
```

## Адаптер поставщика `memory`

Используя `setData()` можно задать массив данных с пользователями, который имеет вид:

```php 
[
    ["username" =>"admin", "name" => "admin", 'password' => 'admin', "email" => "admin@admin.ru"],
    ["username" => "user", "name" => "user", 'password' => 'user', "email" => "user@user.ru"],
]
```

```php 
$di->setShared("auth", function () {

    $security = $this->getSecurity();

    $data = [
        ["auth_token" => '1', "name" => "admin", "username" => "admin", 'password' => 'admin', "email" => "admin@admin.ru"],
        ["auth_token" => '2',  "name" => "admin1", "username" => "admin", 'password' => 'admin1', "email" => "admin1@admin.ru"],
    ];

    $adapter     = new \Sinbadxiii\PhalconAuth\Adapter\Memory($security);
    $adapter->setModel(App\Models\UserSimple::class);
    $adapter->setData($data);
    
    $configGuard = [
        'inputKey'   => 'auth_token',
        'storageKey' => 'auth_token',
    ];

    $guard = new \Sinbadxiii\PhalconAuth\Guard\Token(
        $adapter,
        $configGuard,
        $this->getRequest()
    );
    
    $manager = new Manager();
    $manager->addGuard("api", $guard, true);

    return $manager;
});
```

- public <b>setData</b>(array $data) - массив с данными
- public <b>getData</b>() - получить массив с данными

## Адаптер поставщика `Stream`

Если взять в качестве адаптера поставщиков `users` не `Sinbadxiii\PhalconAuth\Adapter\Model`, а файл `Sinbadxiii\PhalconAuth\Adapter\Stream`:
то необходимо будет задать источник файла формата `json`, например, `users.json`, который имеет вид:

```json 
[
  {"name":"admin", "username":"admin", "password": "admin","email": "admin@admin.ru"},
  {"name":"user", "username":"user", "password": "user","email": "user@user.ru"}
]
```

Возвращаемый пользователь в виде модели `App\Models\UserSimple` должен будет реализовывать интерфейс `Sinbadxiii\PhalconAuth\AuthenticatableInterface`, например:

```php 
<?php

namespace App\Models;

use Phalcon\Auth\AuthenticatableInterface;

class UserSimple implements AuthenticatableInterface
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public string $username;

    /**
     *
     * @var string
     */
    public string $name;

    /**
     *
     * @var string
     */
    public string $email;

    /**
     *
     * @var string
     */
    public string $password;

    /**
     *
     * @var integer
     */
    public $published;

    public function __construct($data)
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }

    /**
     * @return int
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }
}

```

но следует учитывать, что нельзя будет использовать функцию `RememberMe` (Запомнить меня), т.к. `Stream`
не имплементирует интерфейс `Sinbadxiii\PhalconAuth\RememberingInterface` ввиду отсутствия возможности сохранить токен сессии в файлом хранилище пользователей (что не мешает вам реализовать эту функцию в своем охраннике на основе хранилища в файле). 

```php 
    $security = $this->getSecurity();

    $adapter  = new \Sinbadxiii\PhalconAuth\Adapter\Stream($security);
    $adapter->setModel(App\Models\UserSimple::class);
    $adapter->setFileSource(__DIR__ . "/users.json");

    $guard = new \Sinbadxiii\PhalconAuth\Guard\Session(
        $adapter,
        $this->getSession(),
        $this->getCookies(),
        $this->getRequest(),
        $this->getEventsManager()
    );

    $manager = new Manager();
    $manager->addGuard("web", $guard, true); //третий аргумент - назнчаить охранника по дефолту

    return $manager;
```

- public <b>setFileSource</b>(string $pathSrcFile) - указать путь к файлу
- public <b>getFileSource</b>() - получить путь к файлу
- public <b>setData</b>(array $data) - массив с данными пользователей
- public <b>getData</b>() - получить массив с данными пользователей



> Не рекомендуется использовать адаптеры `stream` и `memory` в реальных приложениях из-за их функциональной ограниченности и сложности управления пользователями. Это может быть полезно в прототипах приложений и для ограниченных приложений, которые не хранят пользователей в базах данных.

## Создание своего адаптера поставщика

Интерфейс адаптера поставщика `Sinbadxiii\PhalconAuth\Adapter\AdapterInterface` имеет вид:

```php 
<?php

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;

interface AdapterInterface
{
    public function findFirstByCredentials(array $credentials);
    public function findFirstById($id);
    public function validateCredentials(AuthenticatableInterface $user, array $credentials): bool;
}
```

Так же для создания функционала "Запомнить меня" нужна реализация интерфейса `Sinbadxiii\PhalconAuth\Adapter\AdapterWithRememberTokenInterface`:

```php 
<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuth\Adapter;

use Sinbadxiii\PhalconAuth\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\RememberingInterface;
use Sinbadxiii\PhalconAuth\RememberTokenInterface;

interface AdapterWithRememberTokenInterface
{
    public function findFirstByToken($identifier, $token, $user_agent): ?AuthenticatableInterface;
    public function createRememberToken(RememberingInterface $user): RememberTokenInterface;
}
```

## Manager Factory

`Sinbadxiii\PhalconAuth\MangerFactory` - это создание менеджера аутентификации с минимальными усилиями, если вы не хотите настраивать вручную менеджер аутентификации, а хотите быстро запустить сервис провайдер аутентификации, вы можете сделать это так:
```php
$di->setShared('auth', function () { 
   
    $manager = new \Sinbadxiii\PhalconAuth\ManagerFactory();
    
    return $manager;
});
```

Все, дальше `ManagerFactory` сделает все за вас, на основе вашего конфигурационного файла. По умолчанию используется конфигурация из `$this->config->auth`, если вы хотите использовать другую конфигурацию, отличную от `$this->config->auth` то можно передать в качестве первого аргумента другой конфиг:

```php
$di->setShared("auth", function () {
    $config = $this->getConfig()->auth_config_other;

    $manager = new \Sinbadxiii\PhalconAuth\ManagerFactory($config->toArray());

    return $manager;
});
```
### Пример конфигурационного файла для использования Сессий

Итак, типичный пример конфигурационного файла приложения на основе Сессий. Файл может находится в папке конфигов `config/auth.php` или в глобальном файле `config.php` с доступом по ключу `auth` (`$this->config->auth`).

```php
<?php
[
    'auth' => [
        'defaults' => [ //дефолтные значения
            'guard' => 'web'  //дефолтный охранник
        ],
        'guards' => [   //список охранников
            'web' => [          
                'driver' => 'session',   //драйвер сессия
                'provider' => 'users',   //поставщики users
            ],
        ],
        'providers' => [
            'users' => [
                'adapter' => 'model',  //адаптер поставщика users - model
                'model'  => App\Models\User::class,   //модель
            ]
        ]
    ],
];
```

Т.е. приложение будет использовать `guard = web`. В свою очередь Охранник web основан на драйвере `session` и использует поставщика пользователей `users`, которые извлекаются через Адаптера `model` - `App\Models\Users`.
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
                'inputKey' => 'auth_token', //опционально, по дефолту auth_token
                'storageKey' => 'auth_token', //опционально, по дефолту auth_token
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

Расширить охранников можно с помощью `extendGuard` и передать в качестве аргумента имя охранника, используемого в конфиге  `jwt`, а так же `Closure` с передачей аргументов в новый класс охранника, например:

```php
$di->setShared('auth', function () {
    $auth = new \Sinbadxiii\PhalconAuth\ManagerFactory();    
    
    $request = $this->getRequest();

    $manager->extendGuard("jwt", function ($adapter, $config) use ($request) {
        return new JwtGuard($adapter, $config, $request);
    });
    
    return $auth;
});
```

Вы можете расширить список адаптеров с помощью метода `extendProviderAdapter`, например:

```php 
$di->setShared("auth", function () {
    $authManager =  new Phalcon\Auth\ManagerFactory();

    $authManager->extendProviderAdapter("mongo", function($security, $configProvider) {
        return new App\Security\Adapter\Mongo($security, $configProvider);
    });

    return $authManager;
});
```


## Методы

### Задать требуемый доступ к контроллеру

Метод `access()` позволит задать требуемый доступ к контроллеру, из коробки `auth` - для аутентифицированных, `guest` - для гостей. 

```php
$this->auth->access("auth") 
```
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

### Проверка аутентификации текущего пользователя

Чтобы определить, аутентифицирован ли пользователь, выполняющий входящий HTTP-запрос, вы можете использовать метод `check()`. Этот метод вернет true, если пользователь аутентифицирован:
```php
$this->auth->check(); 
//check authentication
```
Например, вы можете проверить на странице формы входа, что если пользователь вошел в систему, то не показывать ему форму ввода:

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

Метод `attempt()` вернет `true`, если аутентификация прошла успешно. В противном случае будет возвращено `false`.

### Указание дополнительных учетных данных

Вы также можете добавить запрос дополнительных данные в дополнение к электронной почте/имени пользователя и паролю. Для этого просто добавьте условия запроса в массив, переданный методу `attempt()`. Например, мы можем проверить, является ли пользователь опубликованным `is_published`:

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

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;
use Sinbadxiii\PhalconAuth\Exception;

class AuthWithBasic extends AbstractAccess
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

use Sinbadxiii\PhalconAuth\Access\AbstractAccess;
use Sinbadxiii\PhalconAuth\Exception;

class AuthWithBasic extends AbstractAccess
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
