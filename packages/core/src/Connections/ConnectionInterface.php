<?php

namespace KafkaBus\Core\Connections;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Producers\ProducerInterface;
use KafkaBus\Core\Topics\Topic;

interface ConnectionInterface
{
    public function getName(): string;

    public function getOptions(): Options;

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface;

    /**
     * @param ConsumerConfig $config
     * @return ConsumerInterface
     */
    public function createConsumer(ConsumerConfig $config): ConsumerInterface;
}
