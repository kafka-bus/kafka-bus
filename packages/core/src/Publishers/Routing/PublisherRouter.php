<?php

declare(strict_types=1);

namespace KafkaBus\Core\Publishers\Routing;

use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Exceptions\Producers\RouteProducerException;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\MessageBatch;
use KafkaBus\Core\Publishers\PublisherStreamFactoryInterface;
use KafkaBus\Core\Publishers\PublisherStreamInterface;
use KafkaBus\Core\Routing\AbstractRouter;
use KafkaBus\Core\Routing\RouteCollection;
use Throwable;

/**
 * @extends AbstractRouter<Route, PublisherStreamInterface>
 */
final class PublisherRouter extends AbstractRouter
{
    /**
     * @param ConnectionInterface $connection
     * @param PublisherStreamFactoryInterface $producerStreamFactory
     * @param RouteCollection<Route> $routes
     */
    public function __construct(
        protected ConnectionInterface             $connection,
        protected PublisherStreamFactoryInterface $producerStreamFactory,
        RouteCollection                           $routes
    ) {
        parent::__construct($routes);
    }

    /**
     * @template TMessage of ProducerMessageInterface
     * @param MessageBatch<TMessage> $messageBatch
     * @return void
     *
     * @throws RouteProducerException
     */
    public function publish(MessageBatch $messageBatch): void
    {
        $this->resolve($messageBatch->class())
            ->handle($messageBatch->messages());
    }

    /**
     * @template TMessage of ProducerMessageInterface
     * @param Route<TMessage> $route
     * @return PublisherStreamInterface<TMessage>
     */
    protected function build(string $key, mixed $route): PublisherStreamInterface
    {
        return $this->producerStreamFactory
            ->create($this->connection, $route);
    }

    protected function routeNotFound(string $key): Throwable
    {
        return new RouteProducerException("Route for message [$key] not found");
    }
}
