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
 * Route matching result enumeration.
 *
 * Represents the possible outcomes when matching a path and HTTP method
 * against registered routes.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
enum MatchResult: int
{
    /**
     * Route was found and matched successfully.
     * The match result array will contain: [FOUND, target, captures?]
     */
    case FOUND = 0;

    /**
     * Path matched a route but the HTTP method is not allowed.
     * The match result array will contain: [NOT_ALLOWED, allowed_methods[]]
     */
    case NOT_ALLOWED = -1;

    /**
     * No route matched the given path.
     * The match result array will contain: [NOT_FOUND]
     */
    case NOT_FOUND = -2;
}
