<?php

namespace KafkaBus\Core\Connections\Registry;

use KafkaBus\Core\Connections\ConnectionInterface;

interface ConnectionRegistryInterface
{
    public function connection(string $connectionName): ConnectionInterface;
}
