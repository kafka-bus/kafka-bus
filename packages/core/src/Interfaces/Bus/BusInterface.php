<?php

namespace KafkaBus\Core\Interfaces\Bus;

interface BusInterface extends ThreadInterface
{
    public function onConnection(string $connectionName): ThreadInterface;
}
