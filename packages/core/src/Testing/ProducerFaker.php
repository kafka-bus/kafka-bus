<?php

namespace KafkaBus\Core\Testing;

use KafkaBus\Core\Interfaces\Producers\ProducerInterface;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;

class ProducerFaker implements ProducerInterface
{
    public function __construct(
        protected ConnectionFaker $connection,
        protected string $topicName,
    ) {
    }

    public function produce(iterable $messages): void
    {
        // @phpstan-ignore-next-line
        $this->connection->publishedMessages[$this->topicName] = [
            ...$this->connection->publishedMessages[$this->topicName] ?? [],
            ...$messages,
        ];
    }
}
