<?php

namespace KafkaBus\Core\Receivers\Messages;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use RdKafka\Message;

final class OriginalMessageFactory implements MessageFactoryInterface
{
    public function fromKafka(ConsumerMessageInterface $message): Message
    {
        return $message->original();
    }
}
