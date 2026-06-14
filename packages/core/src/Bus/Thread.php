<?php

namespace KafkaBus\Core\Bus;

use KafkaBus\Core\Bus\Listeners\Listener;
use KafkaBus\Core\Bus\Listeners\ListenerFactory;
use KafkaBus\Core\Bus\Publishers\Publisher;
use KafkaBus\Core\Bus\Publishers\PublisherFactory;
use KafkaBus\Core\Interfaces\Bus\ThreadInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

final class Thread implements ThreadInterface
{
    protected Publisher $publisher;

    public function __construct(
        protected ConnectionInterface $connection,
        protected ListenerFactory     $listenerFactory,
        PublisherFactory              $publisherFactory,
    ) {
        $this->publisher = $publisherFactory->create($this->connection);
    }

    public function routes(): array
    {
        return $this->publisher
            ->routes();
    }

    public function publish(ProducerMessageInterface $message): void
    {
        $this->publishBatch(MessageBatch::fromArray([$message]));
    }

    public function publishBatch(MessageBatch $messageBatch): void
    {
        $this->publisher
            ->publish($messageBatch);
    }

    public function listener(string|array $name): Listener
    {
        return $this->listenerFactory
            ->create($this->connection, $name);
    }
}
