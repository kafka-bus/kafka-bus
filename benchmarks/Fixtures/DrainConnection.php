<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures;

use KafkaBus\Core\Connections\Config\Options;
use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Producers\ProducerInterface;
use KafkaBus\Core\Topics\Topic;

/**
 * Соединение без Kafka (в отличие от core NullConnection, продюсер вычитывает поток — отрабатывают middleware): изолирует стоимость самого пакета от брокера и сети.
 */
final class DrainConnection implements ConnectionInterface
{
    public function getName(): string
    {
        return 'null';
    }

    public function getOptions(): Options
    {
        return new Options([]);
    }

    public function createProducer(Topic $topic, ProducerConfig $config): ProducerInterface
    {
        return new DrainProducer();
    }

    public function createConsumer(ConsumerConfig $config): ConsumerInterface
    {
        return new ArrayConsumer([]);
    }
}
