<?php

namespace KafkaBus\Core\Publishers;

use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\Routing\Route;

interface PublisherStreamFactoryInterface
{
    /**
     * @template TMessage of ProducerMessageInterface
     *
     * @param ConnectionInterface $connection
     * @param Route<TMessage> $route
     * @return PublisherStreamInterface<TMessage>
     */
    public function create(ConnectionInterface $connection, Route $route): PublisherStreamInterface;
}
