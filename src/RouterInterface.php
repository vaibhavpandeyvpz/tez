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
 * Interface RouterInterface
 * @package Tez
 */
interface RouterInterface
{
    /**
     * @param string|array $methods
     * @param string $path
     * @param mixed $handler
     */
    public function map($methods, $path, $handler);

    /**
     * @param string $prefix
     * @param callable $group
     */
    public function group($prefix, $group);

    /**
     * @param string $path
     * @param mixed $handler
     */
    public function get($path, $handler);

    /**
     * @param string $path
     * @param mixed $handler
     */
    public function head($path, $handler);

    /**
     * @param string $path
     * @param mixed $handler
     */
    public function options($path, $handler);

    /**
     * @param string $path
     * @param mixed $handler
     */
    public function patch($path, $handler);

    /**
     * @param string $path
     * @param mixed $handler
     */
    public function post($path, $handler);

    /**
     * @param string $path
     * @param mixed $handler
     */
    public function put($path, $handler);
}
