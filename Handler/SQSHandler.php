<?php
namespace LLS\Bundle\MonologExtraBundle\Handler;

use AmazonSQS;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

use LLS\Bundle\SQSBundle\Interfaces\MessageInterface;
use LLS\Bundle\SQSBundle\Interfaces\QueueInterface;

/**
 * SQS Log Handler
 *
 * @author Jérémy Jourdin <jeremy.jourdin@lelivrescolaire.fr>
 */
class SQSHandler extends AbstractProcessingHandler
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * Constructor
     *
     * @param QueueInterface $queue  SQS Queue which handle logs
     * @param int            $level  Log level of the handler
     * @param boolean        $bubble Whether or not bubble logs to other handlers
     */
    public function __construct(QueueInterface $queue, $level = Logger::INFO, $bubble = true)
    {
        $this->queue = $queue;

        $level = is_int($level) ? $level : constant('Monolog\Logger::'.strtoupper($level));

        parent::__construct($level, $bubble);
    }

    /**
     * Get Queue
     *
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $queue = $this->getQueue();

        $queue->sendMessage(
            $queue->getMessageFactory()->create($record["formatted"])
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, false);
    }
}