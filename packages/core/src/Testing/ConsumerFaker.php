<?php

namespace KafkaBus\Core\Testing;

use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;
use RdKafka\Message;

final readonly class ConsumerFaker implements ConsumerInterface
{
    /**
     * @param ConnectionFaker $connectionFaker
     * @param ConsumerMessageConverter $consumerMessageConverter
     * @param array<int, Message> $messages
     */
    public function __construct(
        private ConnectionFaker $connectionFaker,
        private ConsumerMessageConverter $consumerMessageConverter,
        private array $messages
    ) {
    }

    public function start(array $topics): iterable
    {
        foreach ($this->messages as $message) {
            yield $this->consumerMessageConverter
                ->fromKafka($message);
        }
    }

    public function stop(): void
    {
    }

    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
        $this->connectionFaker->committedMessages[$consumerMessage->topicName()][] = $consumerMessage;
    }
}
