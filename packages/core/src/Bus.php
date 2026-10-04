<?php

declare(strict_types=1);

namespace KafkaBus\Core;

use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\MessageBatch;
use KafkaBus\Core\Publishers\Publisher;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Receivers\Receiver;
use KafkaBus\Core\Receivers\ReceiverInterface;

final readonly class Bus implements BusInterface
{
    private Publisher $publisher;

    public function __construct(
        private ConnectionInterface $connection,
        PublisherFactory $publisherFactory = new PublisherFactory(),
        private ReceiverInterface $receiver = new Receiver(),
    ) {
        $this->publisher = $publisherFactory->create($this->connection);
    }

    public function connection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function publisher(): Publisher
    {
        return $this->publisher;
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

    public function dispatch(ConsumerMessageInterface $message): void
    {
        $this->receiver
            ->dispatch($message);
    }
}
