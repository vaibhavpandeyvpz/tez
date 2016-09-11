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
 * @param callable $routing
 * @param string $cache
 * @return MatcherInterface
 */
function CreateMatcher($routing, $cache = null)
{
    if (is_string($cache) && is_file($cache)) {
        /** @noinspection PhpIncludeInspection */
        return new Matcher(require($cache));
    }
    call_user_func($routing, $router = new Router());
    if (is_string($cache)) {
        $data = sprintf('<?php%2$s%2$sreturn %1$s;%2$s', var_export($router->getRoutes(), true), PHP_EOL);
        file_put_contents($cache, $data);
    }
    return new Matcher($router->getRoutes());
}
