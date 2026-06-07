<?php

namespace KafkaBus\Core\Consumers\Messages;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;

final class JsonMessageFactory implements MessageFactoryInterface
{
    /**
     * @param ConsumerMessageInterface $message
     * @return array<string|int, mixed>
     */
    public function fromKafka(ConsumerMessageInterface $message): array
    {
        return json_decode($message->payload(), true); // @phpstan-ignore-line
    }
}
