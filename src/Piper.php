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

final class Piper
{
    const FAIL_ON_ERROR = 0;
    const CONTINUE_ON_ERROR = 1;

    /**
     * @var Stage[]
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
     * @param int                    $onError
     *
     * @return Piper
     * @throws \InvalidArgumentException
     */
    public function pipe($pipe, int $onError = self::FAIL_ON_ERROR): self
    {
        if (!is_callable($pipe) && !$pipe instanceof PipeInterface) {
            throw new \InvalidArgumentException(
                __METHOD__.' expects either a callable or an instance of "PipeInterface".'
            );
        }

        $this->stages->enqueue(new Stage($pipe, $onError));

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
                $this->processed->enqueue($stage->getPipe());

                $stage($context);
            } catch (\Throwable $t) {
                if (self::CONTINUE_ON_ERROR === $stage->getOnError()) {
                    continue;
                }

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
