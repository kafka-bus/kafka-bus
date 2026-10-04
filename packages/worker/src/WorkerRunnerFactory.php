<?php

declare(strict_types=1);

namespace KafkaBus\Worker;

use KafkaBus\Core\BusInterface;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Worker\Consumers\ConsumerStreamFactory;
use KafkaBus\Worker\Consumers\ConsumerStreamFactoryInterface;
use KafkaBus\Worker\Exceptions\WorkerException;
use KafkaBus\Worker\Registry\MemoryWorkerRegistry;
use KafkaBus\Worker\Registry\WorkerRegistryInterface;

final readonly class WorkerRunnerFactory
{
    public function __construct(
        private WorkerRegistryInterface        $workerRegistry = new MemoryWorkerRegistry(),
        private ConsumerStreamFactoryInterface $streamFactory = new ConsumerStreamFactory(),
        private WorkerMerger                   $workerMerger = new WorkerMerger(),
    ) {
    }

    /**
     * @param BusInterface $bus
     * @param string|non-empty-list<string> $name
     * @return WorkerRunner
     */
    public function create(BusInterface $bus, string|array $name): WorkerRunner
    {
        $worker = $this->resolveWorker($name);

        $config = new ConsumerConfig(
            additionalOptions: $worker->options->additionalOptions,
            autoCommit: $worker->options->autoCommit,
            consumerTimeout: $worker->options->consumerTimeout,
        );

        $stream = $this->streamFactory
            ->create($bus->connection(), $config, $bus->dispatch(...));

        return new WorkerRunner($stream, $worker->topics);
    }

    /**
     * @param string|non-empty-list<string> $name
     * @return Worker
     */
    private function resolveWorker(string|array $name): Worker
    {
        $name = \is_array($name) && \count($name) === 1 ? $name[0] : $name;

        if (\is_string($name)) {
            return $this->workerRegistry->get($name)
                ?? throw new WorkerException("Worker [$name] not found.");
        }

        $workers = array_map(
            fn (string $workerName): Worker => $this->workerRegistry->get($workerName)
                ?? throw new WorkerException("Worker [$workerName] not found."),
            $name,
        );

        return $this->workerMerger
            ->merge($workers);
    }
}
