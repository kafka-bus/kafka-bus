<?php

namespace KafkaBus\Core\Interfaces\Connections\Topics;

use KafkaBus\Core\Connections\Topics\ConnectionTopic;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Exceptions\TopicNotFoundException;

interface ConnectionTopicsInterface
{
    public function consume(ConsumerConfig $config): ConnectionConsumerTopicsInterface;

    /**
     * @return ConnectionTopic[]
     */
    public function list(): array;

    /**
     * @param string $topicName
     * @return ConnectionTopic
     *
     * @throws TopicNotFoundException
     */
    public function get(string $topicName): ConnectionTopic;
}
