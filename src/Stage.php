<?php

declare(strict_types=1);

/*
 * This file is part of the Piper package.
 *
 * (c) SolidWorx <open-source@solidworx.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SolidWorx\Piper;

final class Stage
{
    /**
     * @var callable|PipeInterface
     */
    private $pipe;

    /**
     * @var int
     */
    private $onError;

    public function __construct($pipe, int $onError)
    {
        $this->pipe = $pipe;
        $this->onError = $onError;
    }

    /**
     * @return callable|PipeInterface
     */
    public function getPipe()
    {
        return $this->pipe;
    }

    /**
     * @return int
     */
    public function getOnError(): int
    {
        return $this->onError;
    }

    public function __invoke(Context $context)
    {
        if ($this->pipe instanceof PipeInterface) {
            $this->pipe->process($context);
        } else {
            $pipe = $this->pipe;
            $pipe($context);
        }
    }
}