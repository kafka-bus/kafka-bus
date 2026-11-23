<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures;

use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

/**
 * Консьюмер над готовым массивом сообщений; O(1) на сообщение.
 */
final class ArrayConsumer implements ConsumerInterface
{
    public int $commits = 0;

    /**
     * @param list<ConsumerMessageInterface> $messages
     */
    public function __construct(private readonly array $messages)
    {
    }

    public function start(array $topics): iterable
    {
        foreach ($this->messages as $message) {
            yield $message;
        }
    }

    public function stop(): void
    {
    }

    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
        $this->commits++;
    }
}
