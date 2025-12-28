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
 * Router interface for HTTP route matching and registration.
 *
 * Defines the contract for routing implementations that support:
 * - Route registration with optional HTTP method restrictions
 * - Route grouping with common prefixes
 * - Path matching with parameter capture
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
interface RouterInterface
{
    /**
     * Group routes with a common prefix.
     *
     * @param  string  $prefix  The prefix to apply to all routes in the group.
     * @param  callable(RouterInterface): void  $callback  Callback function that receives
     *                                                     the router instance to define routes.
     * @return static Returns self for method chaining.
     */
    public function group(string $prefix, callable $callback): static;

    /**
     * Match a path and HTTP method against registered routes.
     *
     * @param  string  $path  The request path to match.
     * @param  string  $method  The HTTP method to match (empty string matches any method).
     * @return array{0: MatchResult, 1: mixed, 2?: array<string, string>}|array{0: MatchResult, 1: array<string>}
     *                                                                                                            Match result array. When FOUND: [result, target, captures?].
     *                                                                                                            When NOT_ALLOWED: [result, allowed_methods[]].
     *                                                                                                            When NOT_FOUND: [result].
     */
    public function match(string $path, string $method): array;

    /**
     * Register a new route.
     *
     * @param  string  $path  Route path pattern.
     * @param  mixed  $target  Route target/handler.
     * @param  string|array<string>|null  $methods  Allowed HTTP methods (null = any method).
     * @return static Returns self for method chaining.
     */
    public function route(string $path, mixed $target, string|array|null $methods = null): static;
}
