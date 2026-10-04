<?php

namespace KafkaBus\Core\Receivers\Messages;

use JsonException;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

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
