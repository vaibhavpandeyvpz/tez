# vaibhavpandeyvpz/tez
Faster but simpler, framework-agnostic, cache-able http router implementation usable with PHP 5.3+.

[![Build Status](https://img.shields.io/travis/vaibhavpandeyvpz/tez/master.svg?style=flat-square)](https://travis-ci.org/vaibhavpandeyvpz/tez)

Install
-------
```bash
composer require vaibhavpandeyvpz/tez
```

Usage
-----
Create files named ```.htaccess``` & ```index.php``` in your server's root directory. Put below contents in your ```.htaccess``` file:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L,QSA]
```

Then you can define your routes & dispatching mechanism in ```index.php``` similarly as shown below:

```php
<?php

$matcher = Tez\CreateMatcher(function (Tez\Router $router) {
    $router->map(null, '/home', 'dummy_handler');
    $router->post('/home', 'dummy_handler');
    /** @desc You can extract attributes from uri using {...} syntax */
    $router->get('/hello/{name}', 'dummy_handler');
    $router->group('/hi', function (Tez\Router $router) {
        $router->get('/fi', 'dummy_handler');
        $router->get('/{name}', 'dummy_handler');
    });
}, /** [Optional] To use caching */ __DIR__ . '/cache.php');

/** @desc Match the URI against the $_SERVER super-global */
$result = $matcher->match(strtoupper($_SERVER['REQUEST_METHOD']), $_SERVER['REQUEST_URI']);

switch ($result[0]) {
    case Tez\MatcherInterface::RESULT_FOUND:
        if (is_callable($result[1])) {
            /** @desc Call the route handler and process request */
            call_user_func_array($result[1], count($result) == 3 ? $result[2] : array());
        } else {
            throw new InvalidArgumentException('Route handler must be a valid callable');
        }
        break;
    case Tez\MatcherInterface::RESULT_NOT_ALLOWED:
        http_response_code(405);
        echo 'Method Not Allowed';
        break;
    case Tez\MatcherInterface::RESULT_NOT_FOUND:
        http_response_code(404);
        echo 'Not Found';
        break;
}
```

License
------
See [LICENSE.md](https://github.com/vaibhavpandeyvpz/tez/blob/master/LICENSE.md) file.
