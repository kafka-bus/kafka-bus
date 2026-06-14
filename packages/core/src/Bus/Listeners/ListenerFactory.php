<?php

namespace KafkaBus\Core\Bus\Listeners;

use KafkaBus\Core\Bus\Listeners\Workers\MemoryWorkerRegistry;
use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Bus\Listeners\Workers\WorkerMerger;
use KafkaBus\Core\Consumers\ConsumerStreamFactory;
use KafkaBus\Core\Consumers\Handlers\MessageHandlerFactory;
use KafkaBus\Core\Exceptions\Listeners\ListenerException;
use KafkaBus\Core\Interfaces\Bus\Listeners\WorkerRegistryInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerStreamFactoryInterface;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerFactoryInterface;

class ListenerFactory
{
    private WorkerMerger $workerMerger;

    public function __construct(
        protected ConsumerStreamFactoryInterface $streamFactory = new ConsumerStreamFactory(new MessageHandlerFactory()),
        protected WorkerRegistryInterface $workerRegistry = new MemoryWorkerRegistry(),
        MessageHandlerFactoryInterface $messageHandlerFactory = new MessageHandlerFactory(),
    ) {
        $this->workerMerger = new WorkerMerger($messageHandlerFactory);
    }

    /**
     * @param ConnectionInterface $connection
     * @param string|non-empty-list<string> $name
     * @return Listener
     */
    public function create(ConnectionInterface $connection, string|array $name): Listener
    {
        $worker = $this->createWorker($name);
        $consumerStream = $this->streamFactory->create($connection, $worker);

        return new Listener($worker, $connection, $consumerStream);
    }

    /**
     * @param string|non-empty-list<string> $name
     * @return Worker
     */
    private function createWorker(string|array $name): Worker
    {
        $name = \is_array($name) && \count($name) === 1 ? $name[0] : $name;

        if (\is_string($name)) {
            return $this->workerRegistry->get($name)
                ?? throw new ListenerException("Worker [$name] not found.");
        }

        $workers = \array_map(
            fn (string $workerName): Worker => $this->workerRegistry->get($workerName)
                ?? throw new ListenerException("Worker [$workerName] not found."),
            $name,
        );

        return $this->workerMerger
            ->merge($workers);
    }
}
