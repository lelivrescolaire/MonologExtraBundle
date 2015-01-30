<?php
namespace LLS\Bundle\MonologExtraBundle\Tests\Units\Processor;

use mageekguy\atoum\test;
use Monolog\Handler\AbstractProcessingHandler;

use LLS\Bundle\MonologExtraBundle\Processor;

use Monolog\Logger;

class IPRequestProcessor extends test
{
    public function testInstanciate()
    {
        $this
            ->given($request = $this->getRequestMock())
            ->and($processor = new Processor\IPRequestProcessor($request))
            ->then
                ->object($processor->getRequest())
                    ->isIdenticalTo($request)
        ;
    }

    public function testProcessRecord()
    {
        $this
            ->given($request = $this->getRequestMock())
            ->and($processor = new Processor\IPRequestProcessor($request))
            ->then
                ->array($record = $processor->processRecord(array('extra' => array())))
                    ->hasSize(1)
                    ->mock($request)
                        ->call('getClientIp')
                            ->once()
                    ->array($record['extra'])
                        ->isEqualTo(array(
                            'ip' => '192.168.0.1'
                        ))
        ;
    }

    protected function getRequestMock()
    {
        $mock = new \mock\Symfony\Component\HttpFoundation\Request();

        $mock->getMockController()->getClientIp = function () {
            return '192.168.0.1';
        };

        return $mock;
    }
}