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
    const MATCH_NOT_ALLOWED = -1;
    const MATCH_NOT_FOUND = -2;
    const REGEXP_CAPTURE = '#{
        ([^:}]+)
        (?::
            ([^}]+)
        )?
    }#x';
    const REGEXP_SLUG = '[^/]+';

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
     * @var array|bool
     */
    protected $compiled = false;

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $routes = [];

    /**
     * Router constructor.
     * @param array|null $precompiled
     */
    public function __construct(array $precompiled = null)
    {
        $this->compiled = $precompiled !== null ? $precompiled : false;
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        if ($this->compiled !== false) {
            return $this->compiled;
        }
        $compiled = [];
        foreach ($this->routes as $route) {
            if (strpos($route[0], '{') === false) {
                $compiled[] = $route;
                continue;
            }
            $captures = [];
            $regexp = preg_replace_callback(
                self::REGEXP_CAPTURE,
                function (array $match) use (&$captures) {
                    $captures[] = $capture = $match[1];
                    if (empty($match[2])) {
                        $regexp = self::REGEXP_SLUG;
                    } elseif (isset(static::$assertions[$match[2]])) {
                        $regexp = static::$assertions[$match[2]];
                    } else {
                        throw new \RuntimeException("Capture assertion '{$match[2]}' is invalid.");
                    }
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
    public function match($path, $method)
    {
        $allowed = [];
        foreach ($this->compile() as $route) {
            if (empty($route[3])) {
                if ($route[0] === $path) {
                    if (empty($method) || empty($route[1]) || in_array($method, $route[1])) {
                        return [self::MATCH_FOUND, $route[2]];
                    }
                    $allowed = array_merge($allowed, $route[1]);
                }
            } elseif (preg_match($route[3], $path, $matches)) {
                if (empty($method) || empty($route[1]) || in_array($method, $route[1])) {
                    $captures = array_intersect_key($matches, array_flip($route[4]));
                    return [self::MATCH_FOUND, $route[2], $captures];
                }
                $allowed = array_merge($allowed, $route[1]);
            }
        }
        return count($allowed) ? [self::MATCH_NOT_ALLOWED, array_unique($allowed)] : [self::MATCH_NOT_FOUND];
    }

    /**
     * {@inheritdoc}
     */
    public function route($path, $target, $methods = null)
    {
        if (count($this->groups)) {
            $path = implode('', $this->groups) . $path;
        }
        $this->routes[] = [$path, (array)$methods, $target];
        return $this;
    }
}
