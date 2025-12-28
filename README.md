# Tez

Fast and simple enough, framework agnostic, RegExp based HTTP router for micro-services and REST APIs.

> Tez: `तेज` (Fast)

[![Latest Version](https://img.shields.io/packagist/v/vaibhavpandeyvpz/tez.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/tez)
[![Downloads](https://img.shields.io/packagist/dt/vaibhavpandeyvpz/tez.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/tez)
[![PHP Version](https://img.shields.io/packagist/php-v/vaibhavpandeyvpz/tez.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/tez)
[![License](https://img.shields.io/packagist/l/vaibhavpandeyvpz/tez.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/vaibhavpandeyvpz/tez/tests.yml?branch=master&style=flat-square)](https://github.com/vaibhavpandeyvpz/tez/actions)

## Features

- 🚀 **Fast**: Compiled regex patterns with caching for optimal performance
- 🎯 **Simple**: Clean, intuitive API with minimal learning curve
- 🔧 **Flexible**: Framework agnostic - use with any PHP application
- 📝 **Type-safe**: Full PHP 8.2+ type hints and modern language features
- 🎨 **Powerful**: Route parameter capture with custom assertions
- 🔗 **Grouping**: Nested route groups with common prefixes
- ⚡ **Caching**: Precompile routes for production deployments

## Requirements

- PHP 8.2 or higher

## Installation

Install via Composer:

```bash
composer require vaibhavpandeyvpz/tez
```

## Quick Start

```php
<?php

use Tez\Router;
use Tez\MatchResult;

$router = new Router();

// Simple route
$router->route('/', 'HomeController@index');

// Route with parameter
$router->route('/users/{id}', 'UserController@show');

// Route with HTTP method restriction
$router->route('/users', 'UserController@create', 'POST');

// Route groups
$router->group('/api', function (Router $router) {
    $router->route('/users', 'Api\UserController@index', 'GET');
    $router->route('/users/{id}', 'Api\UserController@show', 'GET');
});

// Match a route
$result = $router->match('/users/123', 'GET');

if ($result[0] === MatchResult::FOUND) {
    $target = $result[1];        // 'UserController@show'
    $params = $result[2] ?? [];   // ['id' => '123']
}
```

## Usage

### Basic Routing

```php
$router = new Router();

// Match any HTTP method
$router->route('/about', 'AboutController@index');

// Match specific HTTP methods
$router->route('/users', 'UserController@index', 'GET');
$router->route('/users', 'UserController@create', 'POST');
$router->route('/users', 'UserController@update', ['PUT', 'PATCH']);
```

### Route Parameters

Capture parameters from the URL path:

```php
// Basic parameter capture
$router->route('/users/{id}', 'UserController@show');

// With type assertions
$router->route('/users/{id:i}', 'UserController@show');        // Integer only
$router->route('/users/{username:a}', 'UserController@show');    // Alphabetic only
$router->route('/users/{slug:ai}', 'UserController@show');      // Alphanumeric
$router->route('/colors/{code:h}', 'ColorController@show');     // 6-char hex
$router->route('/files/{path:*}', 'FileController@show');        // Any non-whitespace
```

**Available Assertions:**

- `a` - Alphabetic characters only (`[a-zA-Z]+`)
- `ai` - Alphanumeric characters (`[a-zA-Z0-9]+`)
- `h` - 6-character hexadecimal string (`[a-fA-Z0-9]{6}`)
- `i` - Integer/digits only (`\d+`)
- `*` - Any non-whitespace characters, greedy match (`\S.*`)

### Route Groups

Group routes with a common prefix:

```php
$router->group('/admin', function (Router $router) {
    $router->route('', 'Admin\DashboardController@index');        // /admin
    $router->route('/users', 'Admin\UserController@index');      // /admin/users
    $router->route('/settings', 'Admin\SettingsController@index'); // /admin/settings
});

// Nested groups
$router->group('/api', function (Router $router) {
    $router->group('/v1', function (Router $router) {
        $router->route('/users', 'Api\V1\UserController@index');
    });
    $router->group('/v2', function (Router $router) {
        $router->route('/users', 'Api\V2\UserController@index');
    });
});
```

### Matching Routes

```php
$result = $router->match('/users/123', 'GET');

switch ($result[0]) {
    case MatchResult::FOUND:
        $target = $result[1];        // Route target
        $params = $result[2] ?? [];  // Captured parameters
        // Handle the route
        break;

    case MatchResult::NOT_ALLOWED:
        $allowedMethods = $result[1]; // Array of allowed HTTP methods
        // Return 405 Method Not Allowed
        break;

    case MatchResult::NOT_FOUND:
        // Return 404 Not Found
        break;
}
```

### Route Compilation & Caching

For production, you can precompile routes to improve performance:

```php
// Compile routes
$compiled = $router->compile();

// Save to file
$router->dump('/path/to/routes.php');

// Load precompiled routes
$router = new Router(require '/path/to/routes.php');
```

### Multiple Parameters

Capture multiple parameters in a single route:

```php
$router->route('/users/{userId}/posts/{postId}', 'PostController@show');

$result = $router->match('/users/123/posts/456', 'GET');
// $result[2] = ['userId' => '123', 'postId' => '456']
```

### Different Target Types

Routes can target any type of value:

```php
// String target
$router->route('/home', 'HomeController@index');

// Array target
$router->route('/api', ['controller' => 'Api', 'action' => 'index']);

// Callable target
$router->route('/callback', fn() => 'Hello World');

// Object target
$router->route('/object', new MyHandler());
```

## API Reference

### Router

#### `__construct(?array $precompiled = null)`

Create a new Router instance. Optionally provide precompiled routes for faster initialization.

#### `route(string $path, mixed $target, string|array|null $methods = null): static`

Register a new route.

- `$path` - Route path pattern (e.g., `/users/{id:i}`)
- `$target` - Route target/handler (any type)
- `$methods` - Allowed HTTP methods (string, array, or null for any method)

Returns `$this` for method chaining.

#### `group(string $prefix, callable $callback): static`

Group routes with a common prefix.

- `$prefix` - Prefix to apply to all routes in the group
- `$callback` - Callback function that receives the router instance

Returns `$this` for method chaining.

#### `match(string $path, string $method): array`

Match a path and HTTP method against registered routes.

Returns an array:

- `[MatchResult::FOUND, $target, $params?]` - Route matched
- `[MatchResult::NOT_ALLOWED, $allowedMethods]` - Path matched but method not allowed
- `[MatchResult::NOT_FOUND]` - No route matched

#### `compile(): array`

Compile routes into regex patterns. Results are cached automatically.

#### `dump(string $into): void`

Dump compiled routes to a PHP file for caching.

### MatchResult Enum

- `MatchResult::FOUND` - Route matched successfully
- `MatchResult::NOT_ALLOWED` - Path matched but HTTP method not allowed
- `MatchResult::NOT_FOUND` - No route matched

## Examples

### RESTful API

```php
$router = new Router();

$router->group('/api/users', function (Router $router) {
    $router->route('', 'UserController@index', 'GET');           // GET /api/users
    $router->route('', 'UserController@create', 'POST');          // POST /api/users
    $router->route('/{id:i}', 'UserController@show', 'GET');     // GET /api/users/123
    $router->route('/{id:i}', 'UserController@update', 'PUT');   // PUT /api/users/123
    $router->route('/{id:i}', 'UserController@delete', 'DELETE'); // DELETE /api/users/123
});
```

### Microservice Router

```php
$router = new Router();

// Health check
$router->route('/health', fn() => ['status' => 'ok']);

// API routes
$router->group('/api/v1', function (Router $router) {
    $router->route('/products/{id:i}', 'ProductService@get');
    $router->route('/orders/{orderId:i}/items/{itemId:i}', 'OrderService@getItem');
});
```

## Testing

Run the test suite:

```bash
composer test
```

Or with PHPUnit directly:

```bash
vendor/bin/phpunit
```

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## Author

**Vaibhav Pandey**

- Email: contact@vaibhavpandey.com
- GitHub: [@vaibhavpandeyvpz](https://github.com/vaibhavpandeyvpz)
