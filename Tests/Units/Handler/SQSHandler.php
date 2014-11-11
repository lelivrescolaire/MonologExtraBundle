<?php
namespace LLS\Bundle\MonologExtraBundle\Tests\Units\Handler;

use mageekguy\atoum\test;
use Monolog\Handler\AbstractProcessingHandler;

use LLS\Bundle\MonologExtraBundle\Handler;

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
        $handler = new Handler\SQSHandler($queue = $this->getQueueInterfaceMock(), 'critical', false);

        $this
            ->assert
                ->object($handler->getQueue())
                    ->isIdenticalTo($queue)
                ->integer($handler->getLevel())
                    ->isEqualTo(\Monolog\Logger::CRITICAL)
                ->boolean($handler->getBubble())
                    ->isFalse()
        ;

        $handler = new Handler\SQSHandler($queue = $this->getQueueInterfaceMock(), 25, false);

        $this
            ->assert
                ->object($handler->getQueue())
                    ->isIdenticalTo($queue)
                ->integer($handler->getLevel())
                    ->isEqualTo(25)
                ->boolean($handler->getBubble())
                    ->isFalse()
        ;
    }

    public function testWrite()
    {
        $handler = new Handler\SQSHandler($queue = $this->getQueueInterfaceMock());

        $handler->write(array("formated" => "My log message for SQS"));

        $this
            ->assert
                ->mock($queue->getMessageFactory())
                    ->call('create')
                        ->withArguments("My log message for SQS")
                            ->once
                ->mock($queue)
                    ->call('sendMessage')
                        ->once
        ;
    }

    public function getQueueInterfaceMock()
    {
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

        $mock->getMockController()->create = function ($body) use ($service) {
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