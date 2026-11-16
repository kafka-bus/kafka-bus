<?php

namespace KafkaBus\Messages\Factories;

use JsonException;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Messages\DomainEventEnum;
use KafkaBus\Messages\DomainMessage;

/**
 * @template TMessage of DomainMessage
 */
readonly class DomainMessageFactory implements MessageFactoryInterface
{
    /**
     * @param class-string<TMessage> $messageClass
     */
    public function __construct(
        private string $messageClass,
    ) {
    }

    /**
     * @param ConsumerMessageInterface $message
     * @return TMessage
     *
     * @throws JsonException
     */
    public function fromKafka(ConsumerMessageInterface $message): mixed
    {
        /** @var int<1, max> $depth */
        $depth = ($this->messageClass)::JSON_DEPTH;

        /** @var array{
         *      event: string|null,
         *      attributes: array<string, mixed>|null,
         *      dirty: list<string>|null
         * } $data */
        $data = json_decode($message->payload(), true, $depth, JSON_THROW_ON_ERROR);

        $event = DomainEventEnum::tryFrom($data['event'] ?? 'create')
            ?: DomainEventEnum::Create;

        $attributes = $data['attributes'] ?? [];

        $dirty = $data['dirty'] ?? [];

        return ($this->messageClass)::create($attributes, $event, $dirty);
    }
}
