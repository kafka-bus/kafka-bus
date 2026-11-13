<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\NativeMessageFactory;

final readonly class HandlerExecutor
{
    private HandlerMetadata $metadata;

    /**
     * @param callable $handler
     * @param MessageFactoryInterface $factory
     */
    public function __construct(
        public mixed $handler,
        private MessageFactoryInterface $factory
    ) {
        $this->metadata = HandlerMetadata::fromHandler($this->handler);
    }

    public static function fromHandler(callable $handler, ?MessageFactoryInterface $factory = null): self
    {
        $factory ??= (new MessageFactoryExtractor())->extract($handler)
            ?? new NativeMessageFactory();

        return new self($handler, $factory);
    }

    public function execute(mixed $message, ConsumerMessageInterface $record): void
    {
        ($this->handler)($message, ...$this->metadata->arguments($record));
    }

    public function fromKafka(ConsumerMessageInterface $message): mixed
    {
        return $this->factory->fromKafka($message);
    }
}
