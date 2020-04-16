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
 * Interface RouterInterface
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 * @package Tez
 */
interface RouterInterface
{
    /**
     * @param string $prefix
     * @param callable $callback
     * @return static
     */
    public function group($prefix, callable $callback);

    /**
     * @param string $path
     * @param string $method
     * @return array
     */
    public function match($path, $method);

    /**
     * @param string $path
     * @param mixed $target
     * @param array|null $methods
     * @return static
     */
    public function route($path, $target, $methods = null);
}
