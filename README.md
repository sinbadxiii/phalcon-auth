# Phalcon Auth



You can see an example of an application with authorization and limit here [sinbadxiii/phalcon-auth-example](https://github.com/sinbadxiii/phalcon-auth-example)

![Banner](https://github.com/sinbadxiii/images/blob/master/phalcon-auth/phalcon-auth-logo.png?raw=true)

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></img></a>
<a href="https://packagist.org/packages/sinbadxiii/phalcon-auth"><img src="https://img.shields.io/packagist/dt/sinbadxiii/phalcon-auth?style=flat-square" alt="Packagist Downloads"></img></a>
<a href="https://github.com/sinbadxiii/phalcon-auth/releases"><img src="https://img.shields.io/github/release/sinbadxiii/phalcon-auth?style=flat-square" alt="Latest Version"></img></a>
</p>

- ~~*Стандартная аутентификация на основе сессий и кук*~~
- ~~*Аутентификация на основе токена*~~
- ~~*Расширение кастомными guard'ами*~~
- ~~*Гостевой доступ к контроллерам*~~
- ~~*[Аутентификация с помощью JWT](https://github.com/sinbadxiii/phalcon-auth-jwt)*~~
- ~~*HTTP Basic аутентификацция*~~
- Активация по email (требуется стандартизировать работу с почтой)
- Восстановление пароля(требуется стандартизировать работу с почтой)
- Перевод документации на английский язык

Phalcon Auth позволит вам создать в своем веб-приложении систему аутентификации.
  
Общая суть системы аутентификации состоит в том, чтобы иметь под рукой "Охранников" (Guard), и "Поставщиков" (Provider), охранники определяют, как пользователи будут проходить аутентификацию, например с помощью стандартных Сессий, хранилища Сессий и файлов Cookie. 

Провайдеры определяют, откуда будут извлекаются пользователи. По-умолчанию 
это конечно же Phalcon\Model и строитель запросов к базе данных.

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

1. Config:

Файл конфигурации аутентификации вашего приложения должен будет либо находится в папке ваших конфигов, например config/auth.php. Либо подключаться в другом месте с доступом по ключу "auth" (`$this->config->auth`). Необходимо будет скопировать данные из папки в библиотеке config/auth.php 

При стандартной конфигурации auth.php - defaults => guard => 'web', а driver = 'session', с помощью формы логина пользователь вводит свое имя пользователя и пароль. Если эти учетные данные верны, приложение сохранит информацию об аутентифицированном пользователе в пользовательском сеансе . Файл cookie, отправленный браузеру, содержит идентификатор сеанса, чтобы последующие запросы к приложению могли связать пользователя с правильным сеансом. После получения файла cookie сеанса приложение извлечет данные сеанса на основе идентификатора сеанса, обратите внимание, что информация аутентификации будет сохранена в сеансе, и будет считать пользователя «аутентифицированным».

Второй вариант, если в конфигурации auth.php будет указано в defaults => guard => 'api', а driver = 'token', данная настройка позволит пройти проверку подлинности для доступа к вашему API приложению, файлы cookie обычно не используются для проверки подлинности из-за отсутствия веб-браузера. Вместо этого удаленная служба отправляет API-токен при каждом запросе. Приложение может проверить входящий токен по таблице допустимых токенов API и «аутентифицировать» запрос как выполняемый пользователем, связанным с этим токеном API.

2. Database:
   
Проведите запросы для создания таблиц в БД из файлов `db/users.sql`, `db/users_remember_tokens.sql` (может когда-нибудь дойдут руки до нормальной миграции :) )

3. Register a service provider `Sinbadxiii\PhalconAuth\AuthProvider`

```php 
$di->setShared('auth', function () {
    return new \Sinbadxiii\PhalconAuth\Auth();
});
```

or

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
...

$di = new FactoryDefault();
...
$authServiceProvider = new AuthProvider();
$authServiceProvider->register($di); 


```

4. Implement your controllers from `Sinbadxiii\PhalconAuth\Middlewares\Accessicate`

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


5. Create middleware extends from `Sinbadxiii\PhalconAuth\Middlewares\Authenticate`

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
            $this->response->redirect("/admin/login")->send();
        }
    }
}
```

and attach it in your dispatcher:

```php
$di->setShared("dispatcher", function () use ($di) {
    $dispatcher = new Dispatcher();

    $eventsManager = $di->getShared('eventsManager');
    $eventsManager->attach('dispatch', new Authenticate());
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});
```

or

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

6. Implement your model Users fom AuthenticatableInterface and RememberingInterface, example:

```php 
namespace Models;

use Phalcon\Di\Di;
use Sinbadxiii\PhalconAuth\RememberToken\RememberTokenModel;
use Sinbadxiii\PhalconAuth\Contracts\AuthenticatableInterface;
use Sinbadxiii\PhalconAuth\Contracts\RememberingInterface;
use Sinbadxiii\PhalconAuth\Contracts\RememberTokenInterface;

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

### Перенаправление неаутентифицированных пользователей

Когда middleware обнаруживает неаутентифицированного пользователя, то выполняет метод `redirectTo()`, по умолчанию редирект идет на нужный вам url (ту же форму логина, например), вы можете изменить это поведение, например вернуть json ответ, если для аутентификации используеся ajax запрос.

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

## Methods

### Проверка аутентификации текущего пользователя

Чтобы определить, аутентифицирован ли пользователь, выполняющий входящий HTTP-запрос, вы можете использовать метод `check()`. Этот метод вернет true если пользователь аутентифицирован:


```php
$this->auth->check(); 
//check authentication
```

### Получение аутентифицированного пользователя

При обработке входящего запроса вы можете получить доступ к аутентифицированному пользователю через метод `user()`. Результатом будет провайдер, указанный в конфиге auth.php, по-стандарту Phalcon\Model Users таблицы users. 

Так же можно запросить идентификатор пользователя (ID), с помощью метода `id()`


```php 
$this->auth->user();
//get the user

$this->auth->id();
//get user id
```

### Попытка аутентификации 

Метод `attempt()` используется для обработки попыток аутентификации из формы «входа в систему» вашего приложения.

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

Метод `attempt()` принимает массив пар ключ/значение в качестве первого аргумента. Значения в массиве будут использоваться для поиска пользователя в таблице базы данных. Итак, в приведенном выше примере пользователь будет извлечен по значению столбца username. Если пользователь найден, хешированный пароль, хранящийся в базе данных, будет сравниваться со значением password, переданным в метод. Вы не должны хешировать значение входящего запроса password, т.к. пароль уже автоматически хешируется, для сравнения его с хешированным паролем в базе данных. Сессия с аутентификацией будет запущена для пользователя, если хешированные пароли совпадают.

Помните, что запрашиваться будут пользователи из вашей базы данных на основе конфигурации "провайдера". В файле конфигурации config/auth.php по умолчанию указан поставщик пользователей Phalcon\Model, и ему дано указание использовать модель \Models\User для получения пользователей. Вы можете изменить эти значения в файле конфигурации в зависимости от потребностей вашего приложения.

Метод `attempt()` возвратит true если аутентификация прошла успешно. В противном случае будет возвращен false.

### Указание дополнительных условий

Вы также можете добавить дополнительные условия запроса в дополнение к email/username и паролю пользователя. Для этого нужно просто добавить условия запроса в массив, переданный методу `attempt()`. Например, мы можем проверить, что пользователь отмечен как «активный»:

```php 
if ($this->auth->attempt(['username' => $username, 'password' => $password, 'published' => 1], $remember)) {

 //success attempt
 ...
}
```

### "Запомнить меня"

Если вы хотите обеспечить функциональность «запомнить меня» в своем приложении, вы можете передать логическое значение в качестве второго аргумента метода attempt.

Когда это значение равно true, то время аутентификация пользователя будет неопределенно долго или до тех пор, пока он не выйдет из системы вручную по logout. Таблица users_remember_tokens содержит строковый столбец token, который будет использоваться для хранения токена «запомнить меня».

```php 
$username = $this->request->getPost("username");
$password = $this->request->getPost("password");
$remember = this->request->getPost('remember') ? true : false;

//attempt login with credentials
if ($this->auth->attempt(['username' => $username, 'password' => $password], $remember)) {

 //success attempt
 ...
}

//fail attempt
```

Используйте метод `viaRemember()`, чтобы проверить, прошел ли пользователь аутентификацию с помощью файла cookie «запомнить меня»

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