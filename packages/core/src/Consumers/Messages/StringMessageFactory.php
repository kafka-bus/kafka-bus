<?php

namespace KafkaBus\Core\Consumers\Messages;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;

final class StringMessageFactory implements MessageFactoryInterface
{
    public function fromKafka(ConsumerMessageInterface $message): string
    {
        return $message->payload();
    }
}
