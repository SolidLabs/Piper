<?php

namespace SolidWorx\Piper\Tests;

use SolidWorx\Piper\Context;
use SolidWorx\Piper\PipeInterface;
use SolidWorx\Piper\Piper;
use SolidWorx\Piper\RollbackInterface;

class PiperTest extends \PHPUnit_Framework_TestCase
{
    public function testPipeCallable()
    {
        $pipe = new Piper();

        $pipe->pipe(function (Context $context) {
            $context->set('abc', 123);
        });

        $this->assertSame(123, $pipe->process()->get('abc'));
    }

    public function testPipe()
    {
        $pipe = new Piper();

        $mock = $this->createMock(PipeInterface::class);

        $mock->expects($this->once())
            ->method('process');

        $pipe->pipe($mock);

        $pipe->process();
    }

    public function testPipeCustomContext()
    {
        $context = new Context();

        $pipe = new Piper();

        $pipe->pipe(function () { });

        $this->assertSame($context, $pipe->process($context));
    }

    public function testRollback()
    {
        $pipe = new Piper();

        $step1 = $this->createMock(RollbackInterface::class);

        $exception = new \Exception;
        $step1->expects($this->once())
            ->method('process')
            ->willThrowException($exception);

        $step1->expects($this->once())
            ->method('rollback');

        $step2 = $this->createMock(RollbackInterface::class);
        $step2->expects($this->never())
            ->method('process');

        $pipe->pipe($step1);

        $this->expectException(\Exception::class);

        $this->assertSame($exception, $pipe->process()->getException());
    }

    public function testSkipError()
    {
        $pipe = new Piper();

        $step1 = $this->createMock(PipeInterface::class);
        $step1->expects($this->once())
            ->method('process')
            ->willThrowException(new \Exception);

        $step2 = $this->createMock(PipeInterface::class);
        $step2->expects($this->once())
            ->method('process');

        $pipe->pipe($step1, Piper::CONTINUE_ON_ERROR);
        $pipe->pipe($step2);

        $this->assertNull($pipe->process()->getException());
    }
}
