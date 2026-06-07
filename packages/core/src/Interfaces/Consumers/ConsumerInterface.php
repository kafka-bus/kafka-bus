<?php

namespace KafkaBus\Core\Interfaces\Consumers;

use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Testing\Exceptions\KafkaMessagesEndedException;

interface ConsumerInterface
{
    /**
     * @throws KafkaMessagesEndedException
     * @throws ConsumerException
     */
    public function getMessage(): ConsumerMessageInterface;

    public function commit(ConsumerMessageInterface $consumerMessage): void;
}
