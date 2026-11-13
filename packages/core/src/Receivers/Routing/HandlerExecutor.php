<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers\Routing;

use Closure;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\NativeMessageFactory;

final readonly class HandlerExecutor
{
    private HandlerMetadata $metadata;

    public function __construct(
        private Closure $handler,
        private MessageFactoryInterface $factory
    ) {
        $this->metadata = HandlerMetadata::fromHandler($this->handler);
    }

    public static function fromHandler(callable $handler, ?MessageFactoryInterface $factory = null): self
    {
        $callback = $handler(...);

        $factory ??= (new MessageFactoryExtractor())->extract(\is_object($handler) ? $handler : $callback)
            ?? new NativeMessageFactory();

        return new self($callback, $factory);
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
