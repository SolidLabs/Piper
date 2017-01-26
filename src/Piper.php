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

class Piper
{
    /**
     * @var PipeInterface[]
     */
    private $stages;

    /**
     * @var PipeInterface[]
     */
    private $processed;

    public function __construct()
    {
        $this->stages = new \SplQueue();
        $this->processed = new \SplQueue();
    }

    /**
     * @param callable|PipeInterface $pipe
     *
     * @return Piper
     * @throws \InvalidArgumentException
     */
    public function pipe($pipe): self
    {
        if (!is_callable($pipe) && !$pipe instanceof PipeInterface) {
            throw new \InvalidArgumentException(
                __METHOD__.' expects either a callable or an instance of "PipeInterface".'
            );
        }

        $this->stages->enqueue($pipe);

        return $this;
    }

    /**
     * @param Context|null $context
     *
     * @return mixed
     * @throws \Throwable
     */
    public function process(Context $context = null): Context
    {
        $context = $context ?? new Context();

        if ($this->stages->isEmpty()) {
            throw new \RuntimeException('There are no stages to process');
        }

        foreach ($this->stages as $stage) {
            try {
                $this->processed->enqueue($stage);

                if ($stage instanceof PipeInterface) {
                    $stage->process($context);
                } else {
                    $stage($context);
                }
            } catch (\Throwable $t) {
                $context->setException($t);
                $this->rollback($context);

                throw $t;
            }
        }

        return $context;
    }

    /**
     * @param Context $context
     */
    public function rollback(Context $context)
    {
        try {
            foreach ($this->processed as $stage) {
                if ($stage instanceof RollbackInterface) {
                    $stage->rollback($context);
                }
            }
        } finally {
            $this->processed = new \SplPriorityQueue();
        }
    }
}
