<?php

/*
 * This file is part of vaibhavpandeyvpz/tez package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.md.
 */

namespace Tez;

/**
 * Class Router
 * @package Tez
 */
class Router
{
    /**
     * @var string[]
     */
    protected $groups = array();

    /**
     * @var Route[]
     */
    protected $routes = array();

    /**
     * @param string $method
     * @param string $path
     * @return array|bool
     */
    public function dispatch($method, $path)
    {
        foreach ($this->routes as $route) {
            $methods = $route->getMethods();
            if (empty($methods) || in_array($method, $methods)) {
                $regex = $route->getRegex();
                if (preg_match("~^{$regex}$~", $path, $matches)) {
                    $args = array_intersect_key($matches, array_flip($route->getParams()));
                    return array($route->getHandler(), $args);
                }
            }
        }
        return false;
    }

    /**
     * @param string $namespace
     * @param \Closure $closure
     */
    public function group($namespace, \Closure $closure)
    {
        array_push($this->groups, trim($namespace, '/'));
        $closure($this);
        array_pop($this->groups);
    }

    /**
     * @param string|array $method
     * @param string $path
     * @param mixed $handler
     * @return Route
     */
    public function map($method, $path, $handler)
    {
        if (count($this->groups)) {
            $path = sprintf('/%s/%s', implode('/', $this->groups), ltrim($path, '/'));
        }
        return $this->routes[] = new Route($method, $path, $handler);
    }

    // <editor-fold desc="Aliases">

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function any($pattern, $handler)
    {
        return $this->map(null, $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function delete($pattern, $handler)
    {
        return $this->map('DELETE', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function get($pattern, $handler)
    {
        return $this->map('GET', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function head($pattern, $handler)
    {
        return $this->map('HEAD', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function patch($pattern, $handler)
    {
        return $this->map('PATCH', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function post($pattern, $handler)
    {
        return $this->map('POST', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return Route
     */
    public function put($pattern, $handler)
    {
        return $this->map('PUT', $pattern, $handler);
    }

    // </editor-fold>
}
