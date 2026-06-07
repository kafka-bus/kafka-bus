<?php

declare(strict_types=1);

namespace KafkaBus\Core\Bus;

use KafkaBus\Core\Bus\Listeners\ListenerFactory;
use KafkaBus\Core\Bus\Publishers\PublisherFactory;
use KafkaBus\Core\Interfaces\Bus\ThreadInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;

final class ThreadFactory
{
    public function __construct(
        protected ListenerFactory  $listenerFactory,
        protected PublisherFactory $publisherFactory,
    ) {
    }

    public function create(ConnectionInterface $connection): ThreadInterface
    {
        return new Thread($connection, $this->listenerFactory, $this->publisherFactory);
    }
}
