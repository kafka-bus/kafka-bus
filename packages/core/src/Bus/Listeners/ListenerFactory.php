<?php

namespace KafkaBus\Core\Bus\Listeners;

use KafkaBus\Core\Bus\Listeners\Workers\MemoryWorkerRegistry;
use KafkaBus\Core\Consumers\ConsumerStreamFactory;
use KafkaBus\Core\Consumers\Handlers\MessageHandlerFactory;
use KafkaBus\Core\Exceptions\Listeners\ListenerException;
use KafkaBus\Core\Interfaces\Bus\Listeners\WorkerRegistryInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerStreamFactoryInterface;

class ListenerFactory
{
    public function __construct(
        protected ConsumerStreamFactoryInterface $streamFactory = new ConsumerStreamFactory(new MessageHandlerFactory()),
        protected WorkerRegistryInterface        $workerRegistry = new MemoryWorkerRegistry(),
    ) {
    }

    public function create(ConnectionInterface $connection, string $listenerWorkerName): Listener
    {
        $worker = $this->workerRegistry->get($listenerWorkerName)
            ?? throw new ListenerException("Worker [$listenerWorkerName] not found.");

        $consumerStream = $this->streamFactory->create($connection, $worker);

        return new Listener($worker, $connection, $consumerStream);
    }
}
