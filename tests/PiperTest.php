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

        $mock = $this->createMock(RollbackInterface::class);

        $exception = new \Exception;
        $mock->expects($this->once())
            ->method('process')
            ->willThrowException($exception);

        $mock->expects($this->once())
            ->method('rollback');

        $pipe->pipe($mock);

        $this->expectException(\Exception::class);

        $this->assertSame($exception, $pipe->process()->getException());
    }
}
