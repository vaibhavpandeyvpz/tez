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
 * Class PathCompilerTest
 */
class PathCompilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tez\PathCompiler
     */
    protected $pathCompiler;

    protected function setUp()
    {
        $this->pathCompiler = new Tez\PathCompiler();
    }

    public function testDynamicRoutes()
    {
        // 1 Params
        $result = $this->pathCompiler->compile('/hello/{name}');
        $this->assertArrayHasKey('params', $result);
        $this->assertArrayHasKey('regex', $result);
        $this->assertCount(1, $result['params']);
        $this->assertEquals(array('name'), $result['params']);
        // 2 Params
        $result = $this->pathCompiler->compile('/user/{user}/posts/{post}');
        $this->assertArrayHasKey('params', $result);
        $this->assertArrayHasKey('regex', $result);
        $this->assertCount(2, $result['params']);
        $this->assertEquals(array('user', 'post'), $result['params']);
    }

    public function testStaticRoutes()
    {
        $result = $this->pathCompiler->compile('/home');
        $this->assertArrayNotHasKey('params', $result);
        $this->assertArrayNotHasKey('regex', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertEquals('/home', $result['path']);
    }
}
