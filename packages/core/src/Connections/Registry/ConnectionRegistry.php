<?php

namespace KafkaBus\Core\Connections\Registry;

use KafkaBus\Core\Connections\Config;
use KafkaBus\Core\Interfaces\Connections\ConnectionConfigInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionRegistryInterface;
use KafkaBus\Core\Exceptions\Connections\ConnectionException;

class ConnectionRegistry implements ConnectionRegistryInterface
{
    public const DEFAULT_CONNECTION_NAME = 'default';

    /**
     * @var array<string, ConnectionInterface>
     */
    protected array $activeConnections = [];

    /**
     * @param array<string, ConnectionConfigInterface> $connectionsConfig
     * @param DriverRegistry $driverRegistry
     */
    public function __construct(
        protected array $connectionsConfig,
        protected DriverRegistry $driverRegistry = new DriverRegistry(),
    ) {
    }

    public function connection(string $connectionName): ConnectionInterface
    {
        if (! isset($this->activeConnections[$connectionName])) {
            $this->activeConnections[$connectionName] = $this->makeConnection($connectionName);
        }

        return $this->activeConnections[$connectionName];
    }

    private function makeConnection(string $connectionName): ConnectionInterface
    {
        return $this->driverRegistry
            ->makeConnection($connectionName, $this->getConnectionConfig($connectionName));
    }

    private function getConnectionConfig(string $connectionName): ConnectionConfigInterface
    {
        return $this->connectionsConfig[$connectionName]
            ?? throw ConnectionException::connectionNotFound($connectionName, array_keys($this->connectionsConfig));
    }

    public static function default(string $host = '127.0.0.1', int $port = 29092): self
    {
        return new self([
            self::DEFAULT_CONNECTION_NAME => new Config\KafkaConnectionConfig("$host:$port"),
            'null' => new Config\NullConnectionConfig(),
        ]);
    }
}
