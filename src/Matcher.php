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
 * Class Matcher
 * @package Tez
 */
class Matcher implements MatcherInterface
{
    /**
     * @var array
     */
    protected $routes;

    /**
     * Matcher constructor.
     * @param array $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function match($method, $path)
    {
        $found = false;
        foreach ($this->routes as $route) {
            if (array_key_exists('params', $route)) {
                if (preg_match($route['regex'], $path, $matches)) {
                    if (empty($route['methods']) || in_array($method, $route['methods'])) {
                        $vars = array_intersect_key($matches, array_flip($route['params']));
                        return array(
                            MatcherInterface::RESULT_FOUND,
                            $route['handler'],
                            $vars
                        );
                    } else {
                        $found = true;
                    }
                }
            } elseif ($route['path'] === $path) {
                if (empty($route['methods']) || in_array($method, $route['methods'])) {
                    return array(
                        MatcherInterface::RESULT_FOUND,
                        $route['handler']
                    );
                } else {
                    $found = true;
                }
            }
        }
        return array($found ? MatcherInterface::RESULT_NOT_ALLOWED : MatcherInterface::RESULT_NOT_FOUND);
    }
}
