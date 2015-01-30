<?php
namespace LLS\Bundle\MonologExtraBundle\Processor;

use Symfony\Component\HttpFoundation\Request;

/**
 * Monolog Processor adding client IP to the logs
 *
 * @author Jérémy Jourdin <jeremy.jourdin@lelivrescolaire.fr>
 */
class IPRequestProcessor
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function processRecord(array $record)
    {
        $record['extra']['ip'] = $this->request->getClientIp();

        return $record;
    }
}