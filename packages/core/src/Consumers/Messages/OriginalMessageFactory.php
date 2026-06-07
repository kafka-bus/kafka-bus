<?php

namespace KafkaBus\Core\Consumers\Messages;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;
use RdKafka\Message;

final class OriginalMessageFactory implements MessageFactoryInterface
{
    public function fromKafka(ConsumerMessageInterface $message): Message
    {
        return $message->original();
    }
}
