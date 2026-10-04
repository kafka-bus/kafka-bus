<?php

namespace KafkaBus\Worker\Consumers;

use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

final readonly class ConsumerStreamFactory implements ConsumerStreamFactoryInterface
{
    /**
     * @param ConnectionInterface $connection
     * @param ConsumerConfig $config
     * @param callable(ConsumerMessageInterface $message): void $dispatcher
     * @return ConsumerStreamInterface
     */
    public function create(ConnectionInterface $connection, ConsumerConfig $config, callable $dispatcher): ConsumerStreamInterface
    {
        return new ConsumerStream($connection->createConsumer($config), $dispatcher);
    }
}
