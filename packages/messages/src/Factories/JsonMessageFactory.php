<?php

namespace KafkaBus\Messages\Factories;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;
use KafkaBus\Messages\JsonMessage;

/**
 * @template TMessage of JsonMessage
 */
readonly class JsonMessageFactory implements MessageFactoryInterface
{
    /**
     * @param class-string<TMessage> $messageClass
     */
    public function __construct(
        private string $messageClass,
    ) {
    }

    /**
     * @return TMessage
     */
    public function fromKafka(ConsumerMessageInterface $message): mixed
    {
        /** @var array<string|int, mixed> $data */
        $data = json_decode($message->payload(), true);

        return ($this->messageClass)::from($data);
    }
}
