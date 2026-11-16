<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

use Closure;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\NativeMessageFactory;
use KafkaBus\Core\Receivers\Routing\MessageFactoryExtractor;

/**
 * @internal
 */
final readonly class MessageRoute
{
    private function __construct(
        public Closure $handler,
        private MessageFactoryInterface $factory,
    ) {
    }

    public static function fromHandler(callable $handler, MessageFactoryInterface|null $factory = null): self
    {
        $factory ??= (new MessageFactoryExtractor())->extract($handler)
            ?? new NativeMessageFactory();

        return new self($handler(...), $factory);
    }

    public function decode(ConsumerMessageInterface $message): mixed
    {
        return $this->factory->fromKafka($message);
    }
}
