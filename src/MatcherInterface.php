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
 * Interface MatcherInterface
 * @package Tez
 */
interface MatcherInterface
{
    const RESULT_FOUND = 200;

    const RESULT_NOT_ALLOWED = 405;

    const RESULT_NOT_FOUND = 404;

    /**
     * @param string $method
     * @param string $path
     * @return array
     */
    public function match($method, $path);
}
