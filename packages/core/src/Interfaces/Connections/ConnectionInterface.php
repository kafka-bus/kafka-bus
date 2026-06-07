<?php

namespace KafkaBus\Core\Interfaces\Connections;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Interfaces\Consumers\ConsumerInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerInterface;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Topics\Topic;

interface ConnectionInterface
{
    public function getName(): string;

    public function getOptions(): Options;

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface;

    /**
     * @param Topic[] $topics
     * @param ConsumerConfig $config
     * @return ConsumerInterface
     */
    public function createConsumer(array $topics, ConsumerConfig $config): ConsumerInterface;
}
