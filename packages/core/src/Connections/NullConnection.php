<?php

namespace KafkaBus\Core\Connections;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerInterface;
use KafkaBus\Core\Producers\NullProducer;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Topics\Topic;

final class NullConnection implements ConnectionInterface
{
    /**
     * @param string $name
     */
    public function __construct(protected string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): Options
    {
        return new Options([]);
    }

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface
    {
        return new NullProducer();
    }

    /**
     * @throws ConsumerException
     */
    public function createConsumer(array $topics, ConsumerConfig $config): ConsumerInterface
    {
        throw new ConsumerException('Cannot create consumer for null connection');
    }
}
