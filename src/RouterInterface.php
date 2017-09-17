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
     * Compiles and replaces any dynamic variables e.g., {name:a} in path with valid RegExp e.g., (?P<name>[a-zA-Z]+)
     * and returns an indexed array of RegExp (usable with preg_* functions) and found variables. If no variable is
     * found, false is returned otherwise.
     *
     * @param string $path The path to compile into regex.
     * @return array|false Array of RegExp and variables if any variables found, otherwise false.
     */
    public function compile($path);

    /**
     * Automatically prefixes the path for group of routes added within the specified callback. Passes the router (self)
     * to the callback as first argument.
     *
     * @param string $prefix The path prefix to prepend to routes added in the callback.
     * @param callable $callback The callback to invoke for adding prefixed routes.
     * @return self Same instance of {@see Router}.
     */
    public function group($prefix, callable $callback);

    /**
     * Tries to match a route from the routes map against specified path and optional method. Returns an with first
     * element always one of {@see Router::MATCH_FOUND}, {@see Router::MATCH_NOT_ALLOWED} or
     * {@see Router::MATCH_NOT_FOUND}. The optional second element is the route definition with any matched variables
     * mapped as third element if first element is {@see Router::MATCH_FOUND} or an array of allowed methods in
     * case of {@see Router::MATCH_NOT_ALLOWED}.
     *
     * @param string $path The path to match with.
     * @param string|null $method The HTTP method to match with once path matches.
     * @return array Array of aforementioned elements.
     */
    public function match($path, $method = null);

    /**
     * Adds a new route to the routes map. The $options can optionally contain a set of extra options to be stored along
     * with route data.
     *
     * @param string $path The path to match to, may optionally contain dynamic variables e.g., /user/{id}.
     * @param mixed $target Can be any value to be used by your dispatching mechanism after successful match.
     * @param array|null $options Any extra options, the "name" and "methods" options are used by the {@see Router}.
     * @return self Same instance of {@see Router}.
     */
    public function route($path, $target, array $options = null);

    /**
     * Returns the internal array of mapped routes.
     *
     * @return array The routes map.
     */
    public function routes();

    /**
     * Reverse-generates and returns the URL for the specified route by optionally replacing any required variables.
     *
     * @param string $for The name of the route specified in $options using {@see RouterInterface::route()}.
     * @param array|null $variables Optional variables to replace in dynamic routes.
     * @return string The generated path.
     * @throws \InvalidArgumentException If a route with specified name is not found.
     * @throws \RuntimeException If a required variable by the route is missing.
     */
    public function url($for, array $variables = null);
}
