<?php

namespace KafkaBus\Core\Receivers\Messages;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

final class StringMessageFactory implements MessageFactoryInterface
{
    public function fromKafka(ConsumerMessageInterface $message): string
    {
        return $message->payload();
    }
}
