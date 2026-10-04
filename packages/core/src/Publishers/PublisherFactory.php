<?php

namespace KafkaBus\Core\Publishers;

use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Publishers\Routing\PublisherRouter;
use KafkaBus\Core\Publishers\Routing\Route;
use KafkaBus\Core\Routing\RouteCollection;

final readonly class PublisherFactory
{
    /**
     * @param PublisherStreamFactoryInterface $producerFactory
     * @param RouteCollection<Route> $routes
     */
    public function __construct(
        protected PublisherStreamFactoryInterface $producerFactory = new PublisherStreamFactory(),
        protected RouteCollection $routes = new RouteCollection(Route::class),
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
