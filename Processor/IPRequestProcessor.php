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

    /**
     * @param Request $request SF2 Request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get SF2 request used by this processor
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Process the given record
     *
     * @param array $record Record to process
     *
     * @return array
     */
    public function processRecord(array $record)
    {
        $record['extra']['ip'] = $this->request->getClientIp();

        return $record;
    }
}