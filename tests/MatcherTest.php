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
 * Class MatcherTest
 */
class MatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tez\Matcher
     */
    protected $matcher;

    protected function setUp()
    {
        $this->matcher = new Tez\Matcher(array(
            array(
                'handler' => 'dummy',
                'methods' => array(),
                'path' => '/home'
            ),
            array(
                'handler' => 'dummy',
                'methods' => array('GET'),
                'path' => '/hello-world'
            ),
            array(
                'handler' => 'dummy',
                'methods' => array('GET'),
                'regex' => '~^/hello-(?P<name>[\\w-_%]+)$~',
                'params' => array('name'),
            ),
            array(
                'handler' => 'dummy',
                'methods' => array('GET', 'POST'),
                'path' => '/login',
            ),
            array(
                'handler' => 'dummy',
                'methods' => array('GET'),
                'regex' => '~^/users/(?P<user>[\\w-_%]+)/posts/(?P<post>[\\w-_%]+)$~',
                'params' => array('user', 'post'),
            ),
        ));
    }

    public function testDynamicMatching()
    {
        $this->assertCount(3, $result = $this->matcher->match('GET', '/hello-vaibhav'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertContains('dummy', $result);
        $this->assertCount(1, $result[2]);
        $this->assertArrayHasKey('name', $result[2]);
        $this->assertEquals('vaibhav', $result[2]['name']);
        $this->assertCount(3, $result = $this->matcher->match('GET', '/users/12/posts/2'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertContains('dummy', $result);
        $this->assertCount(2, $result[2]);
        $this->assertArrayHasKey('user', $result[2]);
        $this->assertArrayHasKey('post', $result[2]);
        $this->assertEquals(12, $result[2]['user']);
        $this->assertEquals(2, $result[2]['post']);
    }

    public function testStaticMatching()
    {
        $this->assertCount(2, $result = $this->matcher->match('GET', '/home'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(2, $result = $this->matcher->match('HEAD', '/home'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(2, $result = $this->matcher->match('OPTIONS', '/home'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(2, $result = $this->matcher->match('PATCH', '/home'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(2, $result = $this->matcher->match('POST', '/home'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(2, $result = $this->matcher->match('PUT', '/home'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(2, $result = $this->matcher->match('GET', '/hello-world'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(1, $result = $this->matcher->match('HEAD', '/hello-world'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(1, $result = $this->matcher->match('OPTIONS', '/hello-world'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(1, $result = $this->matcher->match('PATCH', '/hello-world'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(1, $result = $this->matcher->match('POST', '/hello-world'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(1, $result = $this->matcher->match('PUT', '/hello-world'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(2, $result = $this->matcher->match('GET', '/login'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(1, $result = $this->matcher->match('HEAD', '/login'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(1, $result = $this->matcher->match('OPTIONS', '/login'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(1, $result = $this->matcher->match('PATCH', '/login'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
        $this->assertCount(2, $result = $this->matcher->match('POST', '/login'));
        $this->assertContains(Tez\MatcherInterface::RESULT_FOUND, $result);
        $this->assertCount(1, $result = $this->matcher->match('PUT', '/login'));
        $this->assertContains(Tez\MatcherInterface::RESULT_NOT_ALLOWED, $result);
    }
}
