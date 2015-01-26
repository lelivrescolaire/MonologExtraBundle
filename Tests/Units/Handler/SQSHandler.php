<?php
namespace LLS\Bundle\MonologExtraBundle\Tests\Units\Handler;

use mageekguy\atoum\test;
use Monolog\Handler\AbstractProcessingHandler;

use LLS\Bundle\MonologExtraBundle\Handler;

use Monolog\Logger;

class SQSHandler extends test
{
    public function testClass()
    {
        $this
            ->assert
                ->testedClass()
                    ->hasParent('Monolog\Handler\AbstractProcessingHandler')
        ;
    }

    public function testDefaultsValues()
    {
        $handler = new Handler\SQSHandler($queue = $this->getQueueInterfaceMock());

        $this
            ->assert
                ->object($handler->getQueue())
                    ->isIdenticalTo($queue)
                ->integer($handler->getLevel())
                    ->isEqualTo(\Monolog\Logger::INFO)
                ->boolean($handler->getBubble())
                    ->isTrue()
        ;
    }

    public function testInstanciate()
    {
        $this
            ->given($queue = $this->getQueueInterfaceMock())
            ->and($handler = new Handler\SQSHandler($queue, 'critical', false))
            ->then
                ->object($handler->getQueue())
                    ->isIdenticalTo($queue)
                ->integer($handler->getLevel())
                    ->isEqualTo(\Monolog\Logger::CRITICAL)
                ->boolean($handler->getBubble())
                    ->isFalse()
            ->given($queue = $this->getQueueInterfaceMock())
            ->and($handler = new Handler\SQSHandler($queue, 25, false))
            ->then
                ->object($handler->getQueue())
                    ->isIdenticalTo($queue)
                ->integer($handler->getLevel())
                    ->isEqualTo(25)
                ->boolean($handler->getBubble())
                    ->isFalse()
        ;
    }

    public function testHandle()
    {
        $this
            ->given($queue = $this->getQueueInterfaceMock())
            ->and($handler = new Handler\SQSHandler($queue))
            ->if($handler->handle(array("formated" => "My log message for SQS", "level" => Logger::INFO)) || true)
            ->then
                ->mock($queue->getMessageFactory())
                    ->call('create')
                        ->withArguments('{"formated":"My log message for SQS","level":200}')
                            ->once
                ->mock($queue)
                    ->call('sendMessage')
                        ->once
        ;
    }

    public function getQueueInterfaceMock()
    {
        $this->mockGenerator->orphanize('__construct');

        $mock = new \mock\LLS\Bundle\SQSBundle\Interfaces\QueueInterface();

        $mock->getMockController()->getMessageFactory = $this->getMessageFactoryInterfaceMock();

        return $mock;
    }

    protected function getMessageInterfaceMock()
    {
        return new \mock\LLS\Bundle\SQSBundle\Interfaces\MessageInterface();
    }

    protected function getMessageFactoryInterfaceMock()
    {
        $mock    = new \mock\LLS\Bundle\SQSBundle\Interfaces\MessageFactoryInterface();
        $service = $this;

        $mock->getMockController()->create = function ($body) use ($service, $mock) {
            $message = $service->getMessageInterfaceMock();
            $message->getMockController()->getBody          = $body;
            $message->getMockController()->setId            = $message;
            $message->getMockController()->setReceiptHandle = $message;

            return $message;
        };

        return $mock;
    }

    protected function getSQSInterfaceMock()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\LLS\Bundle\SQSBundle\Interfaces\SQSInterface();
    }
}