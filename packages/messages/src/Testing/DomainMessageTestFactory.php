<?php

namespace KafkaBus\Messages\Testing;

use JsonException;
use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Messages\DomainEventEnum;
use KafkaBus\Messages\DomainMessage;
use KafkaBus\Messages\Factories\DomainMessageFactory;

/**
 * @template TMessage of DomainMessage
 */
abstract class DomainMessageTestFactory extends TestFactory
{
    protected DomainEventEnum $event = DomainEventEnum::Create;

    /**
     * @var class-string<TMessage>
     */
    protected string $messageClass;

    /**
     * @var list<string>
     */
    protected array $dirty = [];

    /**
     * @param array<string, mixed> $extra
     * @return array{
     *     event: string,
     *     attributes: array<string, mixed>,
     *     dirty: string[]
     * }
     */
    public function makeArray(array $extra = []): array
    {
        return [
            'event' => $this->event->value,
            'attributes' => parent::makeArray($extra),
            'dirty' => $this->dirty,
        ];
    }

    /**
     * @param DomainEventEnum $event
     * @return $this
     */
    public function withEvent(DomainEventEnum $event): static
    {
        return $this->immutableSet('event', $event);
    }

    /**
     * @param list<string> $dirty
     * @return $this
     */
    public function withDirty(array $dirty): static
    {
        return $this->immutableSet('dirty', $dirty);
    }

    /**
     * @param array<string, mixed> $extra
     * @return TMessage
     *
     * @throws JsonException
     */
    public function message(array $extra = []): DomainMessage
    {
        return (new DomainMessageFactory($this->messageClass))
            ->fromKafka(new ConsumerMessage($this->make($extra)));
    }
}
