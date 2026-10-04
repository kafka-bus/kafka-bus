<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Testing\Exceptions\KafkaMessagesEndedException;

interface ConsumerInterface
{
    /**
     * @param list<string> $topicNames
     * @return void
     */
    public function subscribe(array $topicNames): void;

    /**
     * @return void
     */
    public function unsubscribe(): void;

    /**
     * @throws KafkaMessagesEndedException
     * @throws ConsumerException
     */
    public function getMessage(): ConsumerMessageInterface;

    public function commit(ConsumerMessageInterface $consumerMessage): void;
}
