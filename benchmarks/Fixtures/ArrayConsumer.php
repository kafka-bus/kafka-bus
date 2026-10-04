<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures;

use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Testing\Exceptions\KafkaMessagesEndedException;

/**
 * Консьюмер над готовым массивом сообщений; O(1) на сообщение.
 */
final class ArrayConsumer implements ConsumerInterface
{
    public int $commits = 0;

    private int $position = 0;

    /**
     * @param list<ConsumerMessageInterface> $messages
     */
    public function __construct(private readonly array $messages)
    {
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function subscribe(array $topicNames): void
    {
    }

    public function unsubscribe(): void
    {
    }

    public function getMessage(): ConsumerMessageInterface
    {
        return $this->messages[$this->position++] ?? throw new KafkaMessagesEndedException();
    }

    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
        $this->commits++;
    }
}
