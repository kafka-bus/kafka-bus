<?php

namespace KafkaBus\Core\Publishers;

use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Producers\ProducerConfig;
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\Core\Publishers\Routing\Route;

final readonly class PublisherStreamFactory implements PublisherStreamFactoryInterface
{
    public function create(ConnectionInterface $connection, Route $route): PublisherStreamInterface
    {
        $configuration = $this->makeProducerConfiguration($route->options);

        return new PublisherStream($route, $connection->createProducer($route->topic, $configuration));
    }

    private function makeProducerConfiguration(Options $options): ProducerConfig
    {
        return new ProducerConfig(
            additionalOptions: $options->additionalOptions,
            flushTimeout: $options->flushTimeout,
            flushRetries: $options->flushRetries,
        );
    }
}
