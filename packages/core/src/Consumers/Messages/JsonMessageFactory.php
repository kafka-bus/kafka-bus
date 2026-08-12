<?php

namespace KafkaBus\Core\Consumers\Messages;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;
use JsonException;

final class JsonMessageFactory implements MessageFactoryInterface
{
    /**
     * @param ConsumerMessageInterface $message
     * @return array<string|int, mixed>
     *
     * @throws JsonException
     */
    public function fromKafka(ConsumerMessageInterface $message): array
    {
        return json_decode($message->payload(), true, flags: JSON_THROW_ON_ERROR); // @phpstan-ignore-line
    }
}
