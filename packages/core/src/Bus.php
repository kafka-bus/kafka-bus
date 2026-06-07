<?php

namespace KafkaBus\Core;

use KafkaBus\Core\Bus\Listeners\Listener;
use KafkaBus\Core\Bus\MessageBatch;
use KafkaBus\Core\Bus\ThreadRegistry;
use KafkaBus\Core\Interfaces\Bus\BusInterface;
use KafkaBus\Core\Interfaces\Bus\ThreadInterface;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

final class Bus implements BusInterface
{
    protected ThreadInterface $thread;

    public function __construct(
        protected ThreadRegistry $threadRegistry,
        string                   $defaultConnection
    ) {
        $this->thread = $this->threadRegistry->thread($defaultConnection);
    }

    public function onConnection(string $connectionName): ThreadInterface
    {
        return $this->threadRegistry
            ->thread($connectionName);
    }

    public function routes(): array
    {
        return $this->thread->routes();
    }

    public function publish(ProducerMessageInterface $message): void
    {
        $this->thread->publish($message);
    }

    public function publishBatch(MessageBatch $messageBatch): void
    {
        $this->thread->publishBatch($messageBatch);
    }

    public function listener(string $listenerWorkerName): Listener
    {
        return $this->thread->listener($listenerWorkerName);
    }
}
