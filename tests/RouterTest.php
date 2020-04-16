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
 * Class RouterTest
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 * @package Tez
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyRoutes()
    {
        $router = new Router();
        $result = $router->match('/', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
    }

    public function testFoundAnyMethod()
    {
        $router = new Router();
        $router->route('/', 'Home.Index');
        $result = $router->match('/', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
    }

    public function testFoundMultipleMethods()
    {
        $router = new Router();
        $router->route('/', 'Home.Index', ['GET', 'POST']);
        $result = $router->match('/', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/', 'POST');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/', 'PUT');
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_NOT_ALLOWED, $result[0]);
        $this->assertInternalType('array', $result[1]);
        $this->assertCount(2, $result[1]);
        $this->assertContains('GET', $result[1]);
        $this->assertContains('POST', $result[1]);
    }

    public function testFoundSpecificMethod()
    {
        $router = new Router();
        $router->route('/', 'Home.Index', 'GET');
        $result = $router->match('/', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/', 'POST');
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_NOT_ALLOWED, $result[0]);
        $this->assertInternalType('array', $result[1]);
        $this->assertCount(1, $result[1]);
        $this->assertContains('GET', $result[1]);
    }

    public function testNotAllowed()
    {
        $router = new Router();
        $router->route('/contact-us', 'ContactUs.Submit', 'POST');
        $result = $router->match('/contact-us', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_NOT_ALLOWED, $result[0]);
        $this->assertInternalType('array', $result[1]);
        $this->assertCount(1, $result[1]);
        $this->assertContains('POST', $result[1]);
    }

    public function testNotFound()
    {
        $router = new Router();
        $router->route('/', 'Home.Index');
        $result = $router->match('/home', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
    }

    public function testGroup()
    {
        $router = new Router();
        $router->route('/', 'Home.Index');
        $router->group('/admin', function (Router $router) {
            $router->route('', 'Admin.Dashboard.Index');
            $router->route('/login', 'Admin.Login.Index', 'GET');
            $router->route('/login', 'Admin.Login.Submit', 'POST');
        });
        $router->group('/api', function () use ($router) {
            $router->route('/users', 'Api.Users.Index', 'GET');
            $router->route('/users', 'Api.Users.Create', 'POST');
        });
        $result = $router->match('/', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Home.Index', $result[1]);
        $result = $router->match('/admin', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Admin.Dashboard.Index', $result[1]);
        $result = $router->match('/admin/login', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Admin.Login.Index', $result[1]);
        $result = $router->match('/admin/login', 'POST');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Admin.Login.Submit', $result[1]);
        $result = $router->match('/api/users', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Api.Users.Index', $result[1]);
        $result = $router->match('/api/users', 'POST');
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Api.Users.Create', $result[1]);
    }

    public function testCaptures()
    {
        $router = new Router();
        $router->route('/users/{id}', 'Users.Show');
        $result = $router->match('/users/13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }

    public function testCapturesAssertAlpha()
    {
        $router = new Router();
        $router->route('/users/{username:a}', 'Users.Show');
        $result = $router->match('/users/13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
        $result = $router->match('/users/vpz', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('username', $result[2]);
        $this->assertEquals('vpz', $result[2]['username']);
    }

    public function testCapturesAssertAlphaNumeric()
    {
        $router = new Router();
        $router->route('/users/{username:ai}', 'Users.Show');
        $result = $router->match('/users/vpz.13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
        $result = $router->match('/users/vpz13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('username', $result[2]);
        $this->assertEquals('vpz13', $result[2]['username']);
    }

    public function testCapturesAssertHex()
    {
        $router = new Router();
        $router->route('/colors/{code:h}', 'Colors.Detail');
        $result = $router->match('/colors/13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
        $result = $router->match('/colors/00aeef', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Colors.Detail', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('code', $result[2]);
        $this->assertEquals('00aeef', $result[2]['code']);
    }

    public function testCapturesAssertEverything()
    {
        $router = new Router();
        $router->route('/p/{product:*}', 'Products.Index');
        $result = $router->match('/p/apple-iphone-xs/gold/256-gb', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Products.Index', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('product', $result[2]);
        $this->assertEquals('apple-iphone-xs/gold/256-gb', $result[2]['product']);
    }

    public function testCapturesAssertNumeric()
    {
        $router = new Router();
        $router->route('/users/{id:i}', 'Users.Show');
        $result = $router->match('/users/vpz', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
        $result = $router->match('/users/13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }

    public function testCapturesAssertInvalid()
    {
        $router = new Router();
        $router->route('/users/{id:z}', 'Users.Show');
        $this->setExpectedException('RuntimeException');
        $router->match('/users/13', 'GET');
    }

    public function testPrecompiled()
    {
        $router = new Router([
            [
                '/users/{id:i}',
                ['GET'],
                'Users.Show',
                '#^/users/(?P<id>\d+)$#',
                ['id']
            ]
        ]);
        $result = $router->match('/users/vpz', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
        $result = $router->match('/users/13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }

    public function testDump()
    {
        $router1 = new Router();
        $router1->route('/users/{id:i}', 'Users.Show');
        $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('tez');
        $router1->dump($temp);
        /** @noinspection PhpIncludeInspection */
        $router2 = new Router(require $temp);
        $result = $router2->match('/users/vpz', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals(Router::MATCH_NOT_FOUND, $result[0]);
        $result = $router2->match('/users/13', 'GET');
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals(Router::MATCH_FOUND, $result[0]);
        $this->assertEquals('Users.Show', $result[1]);
        $this->assertInternalType('array', $result[2]);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('13', $result[2]['id']);
    }
}
