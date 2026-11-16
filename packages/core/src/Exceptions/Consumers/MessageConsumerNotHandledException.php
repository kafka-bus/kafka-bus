<?php

namespace KafkaBus\Core\Exceptions\Consumers;

use Exception;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use Throwable;

class MessageConsumerNotHandledException extends Exception
{
    public function __construct(public readonly ConsumerMessageInterface $consumerMessage, ?Throwable $previous = null)
    {
        parent::__construct("Message from {$this->consumerMessage->topicName()} not handled", 500, $previous);
    }
}
