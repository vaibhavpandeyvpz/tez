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
 * Class Route
 * @package Tez
 */
class Route
{
    const REGEX_ATTR = '~\\{([a-zA-Z0-9_]+)\\}~';

    /**
     * @var mixed
     */
    protected $handler;

    /**
     * @var string[]
     */
    protected $methods;

    /**
     * @var string[]
     */
    protected $params;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $regex;

    /**
     * Route constructor.
     * @param string|array $method
     * @param string $path
     * @param mixed $handler
     */
    public function __construct($method, $path, $handler)
    {
        $this->methods = (array)$method;
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return null|string
     */
    public function getRegex()
    {
        if (is_null($this->regex)) {
            $params = array();
            $this->regex = preg_replace_callback(
                self::REGEX_ATTR,
                function (array $matches) use (&$params) {
                    $params[] = $matches[1];
                    return "(?P<{$matches[1]}>[\\w-_%]+)";
                },
                $this->path
            );
            $this->params = $params;
        }
        return $this->regex;
    }
}
