<?php

namespace KafkaBus\Core\Bus\Listeners\Workers;

use KafkaBus\Core\Interfaces\Bus\Listeners\WorkerRegistryInterface;

final class MemoryWorkerRegistry implements WorkerRegistryInterface
{
    /**
     * @var array<string, Worker>
     */
    protected array $workers = [];

    public function add(Worker $worker): self
    {
        $this->workers[$worker->name] = $worker;

        return $this;
    }

    public function get(string $workerName): ?Worker
    {
        return $this->workers[$workerName] ?? null;
    }

    /**
     * @return list<Worker>
     */
    public function all(): array
    {
        return array_values($this->workers);
    }

    public static function make(): self
    {
        return new self();
    }
}
