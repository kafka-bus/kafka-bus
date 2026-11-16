<?php

namespace KafkaBus\Messages\Factories;

use JsonException;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
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
     *
     * @throws JsonException
     */
    public function fromKafka(ConsumerMessageInterface $message): mixed
    {
        /** @var int<1, max> $depth */
        $depth = ($this->messageClass)::JSON_DEPTH;

        /** @var array<string|int, mixed> $data */
        $data = json_decode($message->payload(), true, $depth, JSON_THROW_ON_ERROR);

        return ($this->messageClass)::from($data);
    }
}
