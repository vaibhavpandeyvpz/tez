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
 * Class PathCompiler
 * @package Tez
 */
class PathCompiler implements PathCompilerInterface
{
    const REGEX_ATTR = '~\\{([a-zA-Z0-9_]+)\\}~';

    /**
     * {@inheritdoc}
     */
    public function compile($path)
    {
        $params = array();
        $regex = preg_replace_callback(
            self::REGEX_ATTR,
            function(array $matches) use (&$params) {
                $param = $params[] = $matches[1];
                return "(?P<{$param}>[\\w-_%]+)";
            },
            $path
        );
        if (count($params)) {
            return array(
                'params' => $params,
                'regex' => "~^{$regex}$~",
            );
        } else {
            return array('path' => $path);
        }
    }
}
