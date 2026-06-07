<?php

declare(strict_types=1);

namespace KafkaBus\Core\Bus;

use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

/**
 * @template TMessage of ProducerMessageInterface
 */
final class MessageBatch
{
    /**
     * @param class-string<TMessage> $messageClass,
     * @param iterable<TMessage> $messages
     */
    public function __construct(
        protected string $messageClass,
        protected iterable $messages
    ) {
    }

    /**
     * @return class-string<TMessage>
     */
    public function class(): string
    {
        return $this->messageClass;
    }

    /**
     * @return iterable<TMessage>
     */
    public function messages(): iterable
    {
        return $this->messages;
    }

    /**
     * @template TClass of ProducerMessageInterface
     *
     * @param array<TClass> $messages
     * @return self<TClass>
     */
    public static function fromArray(array $messages): self
    {
        return new self(\get_class($messages[0]), $messages);
    }
}
