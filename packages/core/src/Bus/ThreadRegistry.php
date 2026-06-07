<?php

namespace KafkaBus\Core\Bus;

use KafkaBus\Core\Interfaces\Bus\ThreadInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionRegistryInterface;

class ThreadRegistry
{
    /**
     * @var array<string, ThreadInterface>
     */
    protected array $activeThreads = [];

    public function __construct(
        protected ConnectionRegistryInterface $connectionRegistry,
        protected ThreadFactory               $factory,
    ) {
    }

    public function thread(string $connectionName): ThreadInterface
    {
        if (! isset($this->activeThreads[$connectionName])) {
            $this->activeThreads[$connectionName] = $this->makeThread($connectionName);
        }

        return $this->activeThreads[$connectionName];
    }

    private function makeThread(string $connectionName): ThreadInterface
    {
        $connection = $this->connectionRegistry
            ->connection($connectionName);

        return $this->factory->create($connection);
    }
}
