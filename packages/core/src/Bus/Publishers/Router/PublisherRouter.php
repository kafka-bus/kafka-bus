<?php

namespace KafkaBus\Core\Bus\Publishers\Router;

use KafkaBus\Core\Bus\MessageBatch;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerStreamInterface;
use KafkaBus\Core\Interfaces\Producers\ProducerStreamFactoryInterface;
use KafkaBus\Core\Exceptions\Producers\RouteProducerException;

class PublisherRouter
{
    /**
     * @var array<class-string, ProducerStreamInterface>
     */
    protected array $activeProducerStreams = [];

    public function __construct(
        protected ConnectionInterface            $connection,
        protected ProducerStreamFactoryInterface $producerStreamFactory,
        protected PublisherRoutes                $routes
    ) {
    }

    /**
     * @return list<Route>
     */
    public function routes(): array
    {
        return $this->routes->all();
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
        $this->getOrCreateProducerStream($messageBatch->class())
            ->handle($messageBatch->messages());
    }

    /**
     * @template TMessage of ProducerMessageInterface
     * @param class-string<TMessage> $messageClass
     * @return ProducerStreamInterface<TMessage>
     *
     * @throws RouteProducerException
     */
    private function getOrCreateProducerStream(string $messageClass): ProducerStreamInterface
    {
        if (! isset($this->activeProducerStreams[$messageClass])) {
            $this->activeProducerStreams[$messageClass] = $this->createProducerStream($messageClass);
        }

        /** @var ProducerStreamInterface<TMessage> */
        return $this->activeProducerStreams[$messageClass];
    }

    /**
     * @template TMessage of ProducerMessageInterface
     * @param class-string<TMessage> $messageClass
     * @return ProducerStreamInterface<TMessage>
     *
     * @throws RouteProducerException
     */
    private function createProducerStream(string $messageClass): ProducerStreamInterface
    {
        $route = $this->routes->get($messageClass)
            ?? throw new RouteProducerException("Route for message [$messageClass] not found");

        return $this->producerStreamFactory
            ->create($this->connection, $route);
    }
}
