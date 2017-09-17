<?php

/*
 * This file is part of vaibhavpandeyvpz/tez package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Tez;

/**
 * Class RouteTest
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 * @package Tez
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testCompile()
    {
        $router = new Router();

        $result = $router->compile('/');
        $this->assertFalse($result);

        $result = $router->compile('/hello');
        $this->assertFalse($result);

        $result = $router->compile('/hello/{name}');
        $this->assertNotFalse($result);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertInternalType('string', $result[0]);
        $this->assertInternalType('array', $result[1]);

        $result = $router->compile('/hello/{name:a}');
        $this->assertNotFalse($result);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertInternalType('string', $result[0]);
        $this->assertInternalType('array', $result[1]);

        $this->setExpectedException('RuntimeException');
        $router->compile('/hello/{name:hex}');
    }

    public function testGroup()
    {
        $router = new Router();
        $router->group('/user', function (Router $router) {
            $router->route('', 'User.Index', ['name' => 'user']);
            $router->route('/transactions', 'User.Transactions', ['name' => 'user_transactions']);
            $router->route('/transactions/{id:i}', 'User.Transactions', ['name' => 'user_transactions_one']);
        });
        $this->assertEquals('/user', $router->url('user'));
        $this->assertEquals('/user/transactions', $router->url('user_transactions'));
        $this->assertEquals('/user/transactions/13', $router->url('user_transactions_one', ['id' => 13]));
    }

    public function testMatch()
    {
        $router = new Router();
        $router->route('/', 'Default.Index');
        $router->route('/contact-us', 'Default.ContactUs', ['methods' => ['GET', 'POST']]);

        $match = $router->match('/');
        $this->assertInternalType('array', $match);
        $this->assertCount(2, $match);
        $this->assertInternalType('int', $match[0]);
        $this->assertEquals(Router::MATCH_FOUND, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertEquals('Default.Index', $match[1][1]);

        $match = $router->match('/contact-us', 'POST');
        $this->assertInternalType('array', $match);
        $this->assertCount(2, $match);
        $this->assertInternalType('int', $match[0]);
        $this->assertEquals(Router::MATCH_FOUND, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertEquals('Default.ContactUs', $match[1][1]);

        $match = $router->match('/about-us');
        $this->assertInternalType('array', $match);
        $this->assertCount(1, $match);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $match[0]);

        $match = $router->match('/contact-us', 'PUT');
        $this->assertInternalType('array', $match);
        $this->assertCount(2, $match);
        $this->assertEquals(Router::MATCH_NOT_ALLOWED, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertCount(2, $match[1]);
        $this->assertContains('GET', $match[1]);
        $this->assertContains('POST', $match[1]);
    }

    public function testMatchEverything()
    {
        $router = new Router();
        $router->route('/p/{slug:*}', 'Page.View');
        $match = $router->match('/p/i-love/my-girlfriend');
        $this->assertInternalType('array', $match);
        $this->assertCount(3, $match);
        $this->assertEquals(Router::MATCH_FOUND, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertEquals('Page.View', $match[1][1]);
        $this->assertInternalType('array', $match[2]);
        $this->assertInternalType('array', $match[2]);
        $this->assertArrayHasKey('slug', $match[2]);
        $this->assertEquals('i-love/my-girlfriend', $match[2]['slug']);
    }

    public function testMatchAlpha()
    {
        $router = new Router();
        $router->route('/user/{name:a}', 'User.View');
        $match = $router->match('/user/13');
        $this->assertInternalType('array', $match);
        $this->assertCount(1, $match);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $match[0]);
        $match = $router->match('/user/tez');
        $this->assertInternalType('array', $match);
        $this->assertCount(3, $match);
        $this->assertEquals(Router::MATCH_FOUND, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertEquals('User.View', $match[1][1]);
        $this->assertInternalType('array', $match[2]);
        $this->assertArrayHasKey('name', $match[2]);
        $this->assertEquals('tez', $match[2]['name']);
    }

    public function testMatchAlphaNumeric()
    {
        $router = new Router();
        $router->route('/user/{user:ai}', 'User.View');
        $match = $router->match('/user/tez13');
        $this->assertInternalType('array', $match);
        $this->assertCount(3, $match);
        $this->assertEquals(Router::MATCH_FOUND, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertEquals('User.View', $match[1][1]);
        $this->assertInternalType('array', $match[2]);
        $this->assertArrayHasKey('user', $match[2]);
        $this->assertEquals('tez13', $match[2]['user']);
    }

    public function testMatchHex()
    {
        $router = new Router();
        $router->route('/color/{code:h}', 'Color.Change');
        $match = $router->match('/color/white');
        $this->assertInternalType('array', $match);
        $this->assertCount(1, $match);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $match[0]);
        $match = $router->match('/color/00ad45');
        $this->assertInternalType('array', $match);
        $this->assertCount(3, $match);
        $this->assertEquals(Router::MATCH_FOUND, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertEquals('Color.Change', $match[1][1]);
        $this->assertInternalType('array', $match[2]);
        $this->assertArrayHasKey('code', $match[2]);
        $this->assertEquals('00ad45', $match[2]['code']);
    }

    public function testMatchNumeric()
    {
        $router = new Router();
        $router->route('/user/{id:i}', 'User.View');
        $match = $router->match('/user/tez');
        $this->assertInternalType('array', $match);
        $this->assertCount(1, $match);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $match[0]);
        $match = $router->match('/user/13');
        $this->assertInternalType('array', $match);
        $this->assertCount(3, $match);
        $this->assertEquals(Router::MATCH_FOUND, $match[0]);
        $this->assertInternalType('array', $match[1]);
        $this->assertEquals('User.View', $match[1][1]);
        $this->assertInternalType('array', $match[2]);
        $this->assertArrayHasKey('id', $match[2]);
        $this->assertEquals('13', $match[2]['id']);
    }

    public function testRoutes()
    {
        $router = new Router();
        $router->route('/', 'Default.Index');
        $router->route('/about-us', 'Default.AboutUs');
        $router->route('/contact-us', 'Default.ContactUs');
        $routes = $router->routes();
        $this->assertInternalType('array', $routes);
        $this->assertCount(3, $routes);
    }

    public function testUrl()
    {
        $router = new Router();
        $router->route('/', 'Default.Index', ['name' => 'index']);
        $router->route('/hello/{name}', 'Default.Hello', ['name' => 'hello']);
        $router->route('/user/{id:i}', 'Default.Hello', ['name' => 'user']);
        $this->assertEquals('/', $router->url('index'));
        $this->assertEquals('/hello/tez', $router->url('hello', ['name' => 'tez']));
        $this->assertEquals('/user/13', $router->url('user', ['id' => 13]));
        $this->assertEquals('/hello/tez?id=13', $router->url('hello', ['name' => 'tez', 'id' => 13]));
    }

    public function testUrlInvalidRoute()
    {
        $router = new Router();
        $this->setExpectedException('InvalidArgumentException');
        $router->url('invalid');
    }

    public function testUrlMissingVariable()
    {
        $router = new Router();
        $router->route('/user/{id:i}', 'Default.Hello', ['name' => 'user']);
        $this->setExpectedException('RuntimeException');
        $router->url('user');
    }
}
