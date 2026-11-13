<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

use Closure;
use InvalidArgumentException;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use LogicException;
use Psr\Log\LoggerInterface;

final class MessageRoutes
{
    /**
     * @var array<string, MessageRoute>
     */
    private array $byType = [];

    private ?MessageRoute $whenHeaderMissing = null;

    /**
     * @var Closure(UnroutableMessage $messge): void|null
     */
    private ?Closure $unroutableExecutor = null;

    /**
     * @param non-empty-string $type
     * @param callable $handler
     * @param MessageFactoryInterface|null $factory
     * @return $this
     */
    public function on(string $type, callable $handler, MessageFactoryInterface|null $factory = null): self
    {
        if (isset($this->byType[$type])) {
            throw new InvalidArgumentException("Duplicate message type [$type].");
        }

        $this->byType[$type] = MessageRoute::fromHandler($handler, $factory);

        return $this;
    }

    public function whenHeaderMissing(callable $handler, MessageFactoryInterface|null $factory = null): self
    {
        $this->whenHeaderMissing = MessageRoute::fromHandler($handler, $factory);
        return $this;
    }

    /**
     * @param callable(UnroutableMessage $message): void $handler
     * @return $this
     */
    public function onUnroutable(callable $handler): self
    {
        $this->unroutableExecutor = $handler(...);
        return $this;
    }

    public function onUnroutableLog(LoggerInterface $logger): self
    {
        return $this->onUnroutable(static function (UnroutableMessage $unroutable) use ($logger): void {
            $message = $unroutable->message;
            $original = $message->original();

            $logger->error('Cannot route typed Kafka message.', [
                'reason' => $unroutable->reason->value,
                'type' => $unroutable->messageType,
                'topic' => $message->topicName(),
                'partition' => $original->partition,
                'offset' => $original->offset,
            ]);
        });
    }

    public function onUnroutableThrow(): self
    {
        return $this->onUnroutable(static function (UnroutableMessage $unroutable): void {
            throw new UnroutableMessageException($unroutable);
        });
    }

    /**
     * @internal
     */
    public function messageFactory(): HeaderMessageFactory
    {
        return new HeaderMessageFactory($this->byType, $this->whenHeaderMissing);
    }

    /**
     * @internal
     */
    public function dispatch(mixed $message, string|null $type = null): void
    {
        if ($message instanceof UnroutableMessage) {
            if ($this->unroutableExecutor !== null) {
                ($this->unroutableExecutor)($message);
            }

            return;
        }

        $route = $type == null
            ? $this->whenHeaderMissing
            : ($this->byType[$type] ?? null);

        if ($route === null) {
            throw new LogicException("No typed topic handler for message type [$type]");
        }

        ($route->handler)($message);
    }
}
