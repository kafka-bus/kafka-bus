<?php

namespace KafkaBus\Core\Connections;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Connections\Kafka\KafkaConsumerFactory;
use KafkaBus\Core\Connections\Kafka\KafkaProducerFactory;
use KafkaBus\Core\Connections\Topics\Topics;
use KafkaBus\Core\Consumers\Commiters\DefaultCommiter;
use KafkaBus\Core\Consumers\Commiters\VoidCommiter;
use KafkaBus\Core\Consumers\Consumer;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionHasTopicsInterface;
use KafkaBus\Core\Interfaces\Connections\Topics\ConnectionTopicsInterface;
use KafkaBus\Core\Producers\Producer;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Producers\ProducerInterface;
use KafkaBus\Core\Utils\RetryRepeater;
use KafkaBus\Core\Topics\Topic;

class KafkaConnection implements
    ConnectionInterface,
    ConnectionHasTopicsInterface
{
    protected KafkaProducerFactory $producerFactory;

    protected KafkaConsumerFactory $consumerFactory;

    /**
     * @param string $name
     * @param Options $options
     */
    public function __construct(protected string $name, protected Options $options)
    {
        $this->producerFactory = new KafkaProducerFactory($this->options);
        $this->consumerFactory = new KafkaConsumerFactory($this->options);
    }

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface
    {
        return new Producer(
            producer: $this->producerFactory->make($config),
            topic: $topic,
            retryRepeater: new RetryRepeater($config->flushRetries),
            timeout: $config->flushTimeout
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    public function createConsumer(ConsumerConfig $config): ConsumerInterface
    {
        $consumer = $this->consumerFactory->make($config);

        return new Consumer(
            consumer: $consumer,
            commiter: $config->autoCommit ? new DefaultCommiter($consumer) : new VoidCommiter(),
            retryRepeater: new RetryRepeater(),
            consumerTimeout: $config->consumerTimeout,
        );
    }

    public function topics(): ConnectionTopicsInterface
    {
        return new Topics($this->name, $this->options);
    }
}
