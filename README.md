# vaibhavpandeyvpz/tez
Simple, framework-agnostic, http router implementation compatible with PHP 5.3+.

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

/**
 * @desc Create a new router instance
 */
$router = new Tez\Router();

/**
 * @desc Add some routes. You can extract attributes from uri
 *      using {...} syntax
 */
$router->map(['GET', 'POST'], '/', function () {
    return 'GET /';
});

$router->post('/login', function () {
    return 'GET /login';
});

$router->post('/login', function () {
    return 'GET /login';
});

$router->get('/post/{id}', function ($id) {
    return 'GET /post/' . $id;
});

/**
 * @desc Match the URI against the $_SERVER super-global
 */
$method = strtoupper($_SERVER['REQUEST_METHOD']);
$path = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '/';
$result = $router->dispatch($method, $path);

if ($result === false) {
    http_response_code(404);
    exit;
}

/**
 * @desc Print the handler return value to user-agent
 */
list ($handler, $args) = $result;
echo call_user_func_array($handler, $args);
```

License
------
See [LICENSE.md](https://github.com/vaibhavpandeyvpz/tez/blob/master/LICENSE.md) file.
