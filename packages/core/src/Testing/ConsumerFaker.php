<?php

namespace KafkaBus\Core\Testing;

use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;
use KafkaBus\Core\Testing\Exceptions\KafkaMessagesEndedException;
use RdKafka\Message;

final class ConsumerFaker implements ConsumerInterface
{
    /**
     * @param ConnectionFaker $connectionFaker
     * @param ConsumerMessageConverter $consumerMessageConverter
     * @param array<int, Message> $messages
     */
    public function __construct(
        private readonly ConnectionFaker $connectionFaker,
        private readonly ConsumerMessageConverter $consumerMessageConverter,
        private array $messages
    ) {
    }

    public function getMessage(): ConsumerMessageInterface
    {
        if (\count($this->messages) == 0) {
            throw new KafkaMessagesEndedException();
        }

        return $this->consumerMessageConverter
            ->fromKafka(array_shift($this->messages));
    }

    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
        $this->connectionFaker->committedMessages[$consumerMessage->topicName()][] = $consumerMessage;
    }

    public function subscribe(array $topicNames): void
    {
    }

    public function unsubscribe(): void
    {
    }
}
