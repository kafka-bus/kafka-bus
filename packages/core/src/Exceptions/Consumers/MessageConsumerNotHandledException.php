<?php

namespace KafkaBus\Core\Exceptions\Consumers;

use Exception;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use Throwable;

class MessageConsumerNotHandledException extends Exception
{
    public function __construct(public readonly ConsumerMessageInterface $consumerMessage, ?Throwable $previous = null)
    {
        $message = "Message #{$this->consumerMessage->msgId()} from ".
            "{$this->consumerMessage->topicName()} not handled";

        parent::__construct($message, 500, $previous);
    }
}
