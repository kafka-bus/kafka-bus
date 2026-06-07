<?php

namespace KafkaBus\Core\Producers;

use KafkaBus\Core\Bus\Publishers\Router\Options;
use KafkaBus\Core\Bus\Publishers\Router\Route;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerStreamInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerStreamFactoryInterface;

class ProducerStreamFactory implements ProducerStreamFactoryInterface
{
    public function create(ConnectionInterface $connection, Route $route): ProducerStreamInterface
    {
        $configuration = $this->makeProducerConfiguration($route->options);

        return new ProducerStream($route, $connection->createProducer($route->topic, $configuration));
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
