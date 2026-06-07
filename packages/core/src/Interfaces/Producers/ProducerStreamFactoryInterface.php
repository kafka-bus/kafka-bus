<?php

namespace KafkaBus\Core\Interfaces\Producers;

use KafkaBus\Core\Bus\Publishers\Router\Route;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

interface ProducerStreamFactoryInterface
{
    /**
     * @template TMessage of ProducerMessageInterface
     *
     * @param ConnectionInterface $connection
     * @param Route<TMessage> $route
     * @return ProducerStreamInterface<TMessage>
     */
    public function create(ConnectionInterface $connection, Route $route): ProducerStreamInterface;
}
