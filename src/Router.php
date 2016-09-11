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
class Router implements RouterInterface
{
    /**
     * @var string[]
     */
    protected $groups = array();

    /**
     * @var PathCompilerInterface
     */
    protected $pathCompiler;

    /**
     * @var array
     */
    protected $routes = array();

    /**
     * Router constructor.
     * @param PathCompilerInterface $pathCompiler
     */
    public function __construct(PathCompilerInterface $pathCompiler = null)
    {
        $this->pathCompiler = $pathCompiler ?: new PathCompiler();
    }

    /**
     * @return array
     */
    public function &getRoutes()
    {
        return $this->routes;
    }

    // <editor-fold desc="Routing">

    /**
     * {@inheritdoc}
     */
    public function group($prefix, $group)
    {
        array_push($this->groups, trim($prefix, '/'));
        call_user_func($group, $this);
        array_pop($this->groups);
    }

    /**
     * {@inheritdoc}
     */
    public function map($methods, $path, $handler)
    {
        if (count($this->groups)) {
            $path = sprintf('/%s/%s', implode('/', $this->groups), ltrim($path, '/'));
        }
        $route = array(
            'handler' => $handler,
            'methods' => (array)$methods,
        );
        $route += $this->pathCompiler->compile($path);
        $this->routes[] = $route;
    }

    // </editor-fold>

    // <editor-fold desc="Aliases">

    /**
     * {@inheritdoc}
     */
    public function get($path, $handler)
    {
        $this->map('GET', $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function head($path, $handler)
    {
        $this->map('HEAD', $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function options($path, $handler)
    {
        $this->map('OPTIONS', $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function patch($path, $handler)
    {
        $this->map('PATCH', $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function post($path, $handler)
    {
        $this->map('POST', $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $handler)
    {
        $this->map('PUT', $path, $handler);
    }

    // </editor-fold>
}
