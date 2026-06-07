<?php

namespace KafkaBus\Core\Interfaces\Connections;

interface ConnectionRegistryInterface
{
    public function connection(string $connectionName): ConnectionInterface;
}
