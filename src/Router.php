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
 * Fast and simple RegExp-based HTTP router.
 *
 * This router supports:
 * - Simple string route matching
 * - Parameter capture with regex patterns
 * - Route grouping with prefixes
 * - HTTP method restrictions
 * - Route compilation and caching
 * - Precompiled route loading
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final class Router implements RouterInterface
{
    /**
     * Regular expression pattern for matching route parameter placeholders.
     *
     * Matches patterns like {id} or {id:i} where:
     * - First capture group: parameter name
     * - Second capture group (optional): assertion type (a, ai, h, i, *)
     */
    private const REGEXP_CAPTURE = '#{
        ([^:}]+)
        (?::
            ([^}]+)
        )?
    }#x';

    /**
     * Default regex pattern for route parameters without assertions.
     * Matches any non-slash characters.
     */
    private const REGEXP_SLUG = '[^/]+';

    /**
     * Route parameter assertion patterns.
     *
     * Maps assertion types to their regex patterns:
     * - 'a': Alphabetic characters only
     * - 'ai': Alphanumeric characters
     * - 'h': 6-character hexadecimal string
     * - 'i': Integer (digits only)
     * - '*': Any non-whitespace characters (greedy match)
     *
     * @var array<string, string>
     */
    private const ASSERTIONS = [
        'a' => '[a-zA-Z]+',
        'ai' => '[a-zA-Z0-9]+',
        'h' => '[a-fA-Z0-9]{6}',
        'i' => '\d+',
        '*' => '\S.*',
    ];

    /**
     * Compiled route cache.
     *
     * When false, routes need to be compiled.
     * When array, contains compiled routes with regex patterns and capture groups.
     *
     * Route structure: [path, methods, target, regex?, captures?]
     *
     * @var array<int, array{0: string, 1: array<string>|null, 2: mixed, 3?: string, 4?: array<string>}>|false
     */
    private array|false $compiled = false;

    /**
     * Active route group prefixes.
     *
     * Used to build nested route paths when adding routes within groups.
     *
     * @var array<string>
     */
    private array $groups = [];

    /**
     * Registered routes before compilation.
     *
     * Route structure: [path, methods, target]
     * - path: Route path pattern
     * - methods: Allowed HTTP methods (null = any method)
     * - target: Route target (handler, controller, etc.)
     *
     * @var array<int, array{0: string, 1: array<string>|null, 2: mixed}>
     */
    private array $routes = [];

    /**
     * Create a new Router instance.
     *
     * @param  array<int, array{0: string, 1: array<string>|null, 2: mixed, 3?: string, 4?: array<string>}>|null  $precompiled
     *                                                                                                                          Precompiled routes to use instead of compiling from scratch.
     *                                                                                                                          If provided, routes will not be recompiled.
     */
    public function __construct(?array $precompiled = null)
    {
        $this->compiled = $precompiled ?? false;
    }

    /**
     * Compile routes into regex patterns for matching.
     *
     * Converts route patterns with placeholders (e.g., {id:i}) into
     * compiled regex patterns. Results are cached for performance.
     *
     * @return array<int, array{0: string, 1: array<string>|null, 2: mixed, 3?: string, 4?: array<string>}>
     *                                                                                                      Compiled routes with regex patterns and capture groups.
     *                                                                                                      Structure: [path, methods, target, regex?, captures?]
     */
    public function compile(): array
    {
        if ($this->compiled !== false) {
            return $this->compiled;
        }
        $compiled = [];
        foreach ($this->routes as $route) {
            if (! str_contains($route[0], '{')) {
                $compiled[] = $route;

                continue;
            }
            $captures = [];
            $regexp = preg_replace_callback(
                self::REGEXP_CAPTURE,
                function (array $match) use (&$captures): string {
                    $captures[] = $capture = $match[1];
                    $assertion = $match[2] ?? '';
                    $regexp = match ($assertion) {
                        '' => self::REGEXP_SLUG,
                        default => self::ASSERTIONS[$assertion] ?? throw new \RuntimeException("Capture assertion '{$assertion}' is invalid."),
                    };

                    return "(?P<{$capture}>{$regexp})";
                },
                $route[0]
            );
            $route[3] = "#^$regexp$#";
            $route[4] = $captures;
            $compiled[] = $route;
        }

        return $this->compiled = $compiled;
    }

    /**
     * Dump compiled routes to a PHP file.
     *
     * Useful for caching compiled routes to disk for faster loading
     * in production environments.
     *
     * @param  string  $into  File path where compiled routes will be written.
     *                        The file will contain a PHP array that can be
     *                        loaded via require() and passed to the constructor.
     */
    public function dump(string $into): void
    {
        $routes = var_export($this->compile(), true);
        file_put_contents($into, "<?php\n\nreturn {$routes};\n");
    }

    /**
     * Group routes with a common prefix.
     *
     * All routes defined within the callback will have the prefix
     * automatically prepended to their paths. Groups can be nested.
     *
     * @param  string  $prefix  The prefix to apply to all routes in the group.
     * @param  callable(RouterInterface): void  $callback  Callback function that receives
     *                                                     the router instance to define routes.
     * @return static Returns self for method chaining.
     */
    #[\Override]
    public function group(string $prefix, callable $callback): static
    {
        $this->groups[] = $prefix;
        try {
            if ($callback instanceof \Closure) {
                $callback = $callback->bindTo($this);
            }
            $callback($this);
        } finally {
            array_pop($this->groups);
        }

        return $this;
    }

    /**
     * Match a path and HTTP method against registered routes.
     *
     * Returns an array with the match result:
     * - [MatchResult::FOUND, target, captures?] - Route matched successfully
     * - [MatchResult::NOT_ALLOWED, allowed_methods[]] - Path matched but method not allowed
     * - [MatchResult::NOT_FOUND] - No route matched
     *
     * @param  string  $path  The request path to match (e.g., '/users/123').
     * @param  string  $method  The HTTP method to match (e.g., 'GET', 'POST').
     *                          Empty string matches any method.
     * @return array{0: MatchResult, 1: mixed, 2?: array<string, string>}|array{0: MatchResult, 1: array<string>}
     *                                                                                                            Match result array. When FOUND: [result, target, captures?].
     *                                                                                                            When NOT_ALLOWED: [result, allowed_methods[]].
     *                                                                                                            When NOT_FOUND: [result].
     */
    #[\Override]
    public function match(string $path, string $method): array
    {
        $allowed = [];
        foreach ($this->compile() as $route) {
            $regexp = $route[3] ?? null;
            if ($regexp === null) {
                // Simple string match
                if ($route[0] === $path) {
                    $methods = $route[1];
                    // null or empty array means accept any method
                    if ($method === '' || $methods === null || $methods === [] || in_array($method, $methods, true)) {
                        return [MatchResult::FOUND, $route[2]];
                    }
                    if ($methods !== null && $methods !== []) {
                        $allowed = [...$allowed, ...$methods];
                    }
                }
            } elseif (preg_match($regexp, $path, $matches) === 1) {
                // Regex match
                $methods = $route[1];
                // null or empty array means accept any method
                if ($method === '' || $methods === null || $methods === [] || in_array($method, $methods, true)) {
                    $captures = array_intersect_key($matches, array_flip($route[4] ?? []));

                    return [MatchResult::FOUND, $route[2], $captures];
                }
                if ($methods !== null && $methods !== []) {
                    $allowed = [...$allowed, ...$methods];
                }
            }
        }

        return $allowed !== []
            ? [MatchResult::NOT_ALLOWED, array_unique($allowed)]
            : [MatchResult::NOT_FOUND];
    }

    /**
     * Register a new route.
     *
     * Route paths can contain placeholders like {id} or {id:i} for parameter capture.
     * Available assertion types: 'a' (alpha), 'ai' (alphanumeric), 'h' (hex),
     * 'i' (integer), '*' (any non-whitespace).
     *
     * @param  string  $path  Route path pattern (e.g., '/users/{id:i}').
     *                        Empty string is normalized to '/'.
     * @param  mixed  $target  Route target/handler (string, callable, object, etc.).
     * @param  string|array<string>|null  $methods  Allowed HTTP methods.
     *                                              Single string, array of strings, or null for any method.
     *                                              Empty array is normalized to null.
     * @return static Returns self for method chaining.
     */
    #[\Override]
    public function route(string $path, mixed $target, string|array|null $methods = null): static
    {
        if ($this->groups !== []) {
            $path = implode('', $this->groups).$path;
        }
        // Normalize empty path to root
        if ($path === '') {
            $path = '/';
        }
        // Normalize empty array to null (both mean "accept any method")
        $normalizedMethods = $methods !== null ? (array) $methods : null;
        if ($normalizedMethods === []) {
            $normalizedMethods = null;
        }
        $this->routes[] = [$path, $normalizedMethods, $target];
        // Reset compiled cache when new routes are added
        if ($this->compiled !== false) {
            $this->compiled = false;
        }

        return $this;
    }
}
