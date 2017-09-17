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
 * Class Router
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 * @package Tez
 */
class Router implements RouterInterface
{
    const MATCH_FOUND = 0;

    const MATCH_NOT_ALLOWED = 1;

    const MATCH_NOT_FOUND = -1;

    const REGEXP_SLUG = '[^/]+';

    const REGEXP_VAR = '#{
        ([^:}]+)
        (?::
            ([^}]+)
        )?
    }#x';

    /**
     * @var array
     */
    protected static $assertions = [
        'a' => '[a-zA-Z]+',
        'ai' => '[a-zA-Z0-9]+',
        'h' => '[a-fA-Z0-9]{6}',
        'i' => '\d+',
        '*' => '\S.*',
    ];

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $routes;

    /**
     * Router constructor.
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function compile($path)
    {
        $variables = [];
        $regexp = preg_replace_callback(
            self::REGEXP_VAR,
            function (array $match) use (&$variables) {
                $variables[] = $variable = $match[1];
                if (empty($match[2])) {
                    $regexp = self::REGEXP_SLUG;
                } elseif (isset(self::$assertions[$match[2]])) {
                    $regexp = self::$assertions[$match[2]];
                } else {
                    throw new \RuntimeException("Variable assertion '{$match[2]}' is invalid.");
                }
                return "(?P<{$variable}>{$regexp})";
            },
            $path
        );
        return empty($variables) ? false : ["#^$regexp$#", $variables];
    }

    /**
     * {@inheritdoc}
     */
    public function group($prefix, callable $callback)
    {
        $this->groups[] = $prefix;
        if (($callback instanceof \Closure) && (0 <= version_compare(PHP_VERSION, '5.4.0'))) {
            $callback = $callback->bindTo($this);
        }
        call_user_func($callback, $this);
        array_pop($this->groups);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function match($path, $method = null)
    {
        $allowed = [];
        foreach ($this->routes as &$route) {
            if (empty($route['compiled'])) {
                $route['compiled'] = $this->compile($route[0]);
            }
            if (false === $route['compiled']) {
                if ($route[0] === $path) {
                    if (empty($method) || empty($route['methods']) || in_array($method, $route['methods'])) {
                        return [self::MATCH_FOUND, $route];
                    }
                    $allowed = array_merge($allowed, $route['methods']);
                }
            } elseif (preg_match($route['compiled'][0], $path, $matches)) {
                if (empty($method) || empty($route['methods']) || in_array($method, $route['methods'])) {
                    $variables = array_intersect_key($matches, array_flip($route['compiled'][1]));
                    return [self::MATCH_FOUND, $route, $variables];
                }
                $allowed = array_merge($allowed, $route['methods']);
            }
        }
        return count($allowed) ? [self::MATCH_NOT_ALLOWED, array_unique($allowed)] : [self::MATCH_NOT_FOUND];
    }

    /**
     * {@inheritdoc}
     */
    public function route($path, $target, array $options = null)
    {
        if ($this->groups) {
            $path = implode('', $this->groups) . $path;
        }
        $route = [$path, $target];
        if ($options) {
            $route = array_merge($route, $options);
        }
        if (empty($route['name'])) {
            $this->routes[] = $route;
        } else {
            $this->routes[$route['name']] = $route;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function url($for, array $variables = null)
    {
        if (isset($this->routes[$for])) {
            $route = $this->routes[$for];
            if (empty($route['compiled']) || (false !== $route['compiled'])) {
                $path = preg_replace_callback(
                    self::REGEXP_VAR,
                    function (array $matches) use ($for, &$variables) {
                        $variable = $matches[1];
                        if (empty($variables) || empty($variables[$variable])) {
                            throw new \RuntimeException(
                                "Route '$for' requires a value for '$variable' variable, none provided."
                            );
                        }
                        $value = $variables[$variable];
                        unset($variables[$variable]);
                        return $value;
                    },
                    $route[0]
                );
            } else {
                $path = $route[0];
            }
            if ($variables) {
                $path .= ('?' . http_build_query($variables));
            }
            return $path;
        }
        throw new \InvalidArgumentException("No route found named '$for'.");
    }
}
