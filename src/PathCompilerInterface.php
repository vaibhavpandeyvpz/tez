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
 * Interface PathCompilerInterface
 * @package Tez
 */
interface PathCompilerInterface
{
    /**
     * @param string $path
     * @return array
     */
    public function compile($path);
}
