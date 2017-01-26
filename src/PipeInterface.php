<?php

/*
 * This file is part of the Piper package.
 *
 * (c) SolidWorx <open-source@solidworx.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SolidWorx\Piper;

interface PipeInterface
{
    public function process(Context $context);
}