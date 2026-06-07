<?php

namespace KafkaBus\Core\Testing\Connections;

use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionRegistryInterface;

class ConnectionRegistryFaker implements ConnectionRegistryInterface
{
    public function __construct(
        protected ConnectionInterface $connection
    ) {
    }

    public function connection(?string $connectionName = null): ConnectionInterface
    {
        return $this->connection;
    }
}
