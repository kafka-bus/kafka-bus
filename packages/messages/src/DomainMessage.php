<?php

namespace KafkaBus\Messages;

use KafkaBus\Core\Interfaces\Producers\Messages\HasKey;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Messages\Data\Payload;

abstract class DomainMessage extends Payload implements HasKey, ProducerMessageInterface
{
    /**
     * @param array<string, mixed> $attributes
     * @param DomainEventEnum $event
     * @param list<string> $dirty
     */
    public function __construct(
        array                            $attributes,
        private readonly DomainEventEnum $event = DomainEventEnum::Create,
        private readonly array           $dirty = []
    ) {
        parent::__construct($attributes);
    }

    public function getEvent(): DomainEventEnum
    {
        return $this->event;
    }

    /**
     * @return list<string>
     */
    public function getDirty(): array
    {
        return $this->dirty;
    }

    public function toPayload(): string
    {
        return (string) json_encode([
            'event' => $this->event->value,
            'attributes' => $this->jsonSerialize(),
            'dirty' => $this->dirty,
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     * @param DomainEventEnum $event
     * @param list<string> $dirty
     * @return static
     */
    public static function create(array $attributes, DomainEventEnum $event = DomainEventEnum::Create, array $dirty = []): static
    {
        return new static($attributes, $event, $dirty); // @phpstan-ignore-line
    }
}
