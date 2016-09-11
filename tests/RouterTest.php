<?php

/*
 * This file is part of vaibhavpandeyvpz/tez package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.md.
 */

/**
 * Class RouterTest
 */
class RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tez\Router
     */
    protected $router;

    public function setUp()
    {
        $this->router = $router = new Tez\Router();
        $router->get('/home', 'dummy');
        $router->get('/hello-world', 'dummy');
        $router->get('/hello-{name}', 'dummy');
        $router->map(array('GET', 'POST'), '/login', 'dummy');
        $router->get('/users/{user}/posts/{post}', 'dummy');
    }

    public function testDataGeneration()
    {
        $routes = $this->router->getRoutes();
        $this->assertCount(5, $routes);
        $this->assertCount(3, $routes[0]);
        $this->assertArrayHasKey('handler', $routes[0]);
        $this->assertArrayHasKey('methods', $routes[0]);
        $this->assertContains('GET', $routes[0]['methods']);
        $this->assertArrayHasKey('path', $routes[0]);
        $this->assertEquals('/home', $routes[0]['path']);
        $this->assertCount(3, $routes[1]);
        $this->assertArrayHasKey('handler', $routes[1]);
        $this->assertArrayHasKey('methods', $routes[1]);
        $this->assertContains('GET', $routes[1]['methods']);
        $this->assertArrayHasKey('path', $routes[1]);
        $this->assertEquals('/hello-world', $routes[1]['path']);
        $this->assertCount(4, $routes[2]);
        $this->assertArrayHasKey('handler', $routes[2]);
        $this->assertArrayHasKey('methods', $routes[2]);
        $this->assertContains('GET', $routes[2]['methods']);
        $this->assertArrayHasKey('params', $routes[2]);
        $this->assertCount(1, $routes[2]['params']);
        $this->assertEquals(array('name'), $routes[2]['params']);
        $this->assertArrayHasKey('regex', $routes[2]);
        $this->assertCount(3, $routes[3]);
        $this->assertArrayHasKey('handler', $routes[3]);
        $this->assertArrayHasKey('methods', $routes[3]);
        $this->assertContains('GET', $routes[3]['methods']);
        $this->assertContains('POST', $routes[3]['methods']);
        $this->assertArrayHasKey('path', $routes[3]);
        $this->assertCount(4, $routes[4]);
        $this->assertArrayHasKey('handler', $routes[4]);
        $this->assertArrayHasKey('methods', $routes[4]);
        $this->assertContains('GET', $routes[4]['methods']);
        $this->assertArrayHasKey('params', $routes[4]);
        $this->assertCount(2, $routes[4]['params']);
        $this->assertEquals(array('user', 'post'), $routes[4]['params']);
        $this->assertArrayHasKey('regex', $routes[4]);
    }
}
