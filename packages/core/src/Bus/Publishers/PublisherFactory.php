<?php

namespace KafkaBus\Core\Bus\Publishers;

use KafkaBus\Core\Bus\Publishers\Router\PublisherRouter;
use KafkaBus\Core\Bus\Publishers\Router\PublisherRoutes;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerStreamFactoryInterface;
use KafkaBus\Core\Producers\ProducerStreamFactory;

class PublisherFactory
{
    public function __construct(
        protected ProducerStreamFactoryInterface $producerFactory = new ProducerStreamFactory(),
        protected PublisherRoutes                $routes = new PublisherRoutes(),
    ) {
    }

    public function create(ConnectionInterface $connection): Publisher
    {
        return new Publisher(
            new PublisherRouter(
                $connection,
                $this->producerFactory,
                $this->routes
            )
        );
    }
}
