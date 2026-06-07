<?php

namespace KafkaBus\Core\Connections\Registry;

use KafkaBus\Core\Connections\Config\KafkaConnectionConfig;
use KafkaBus\Core\Connections\Config\NullConnectionConfig;
use KafkaBus\Core\Connections\KafkaConnection;
use KafkaBus\Core\Connections\NullConnection;
use KafkaBus\Core\Interfaces\Connections\ConnectionConfigInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Exceptions\Connections\DriverException;

class DriverRegistry
{
    /**
     * @var array<class-string, callable(string, ConnectionConfigInterface): ConnectionInterface>
     */
    protected array $drivers = [];

    public function __construct()
    {
        $this->initDrivers();
    }

    /**
     * @template TConfiguration of ConnectionConfigInterface
     * @param class-string<TConfiguration> $configurationClass
     * @param callable(string, TConfiguration): ConnectionInterface $connectionMaker
     * @return void
     */
    public function add(string $configurationClass, callable $connectionMaker): void
    {
        $this->drivers[$configurationClass] = $connectionMaker; // @phpstan-ignore-line
    }

    protected function initDrivers(): void
    {
        $this->addNullDriver();
        $this->addKafkaDriver();
    }

    private function addNullDriver(): void
    {
        $this->add(
            NullConnectionConfig::class,
            static fn (string $name, NullConnectionConfig $config) => new NullConnection($name)
        );

    }

    private function addKafkaDriver(): void
    {
        $this->add(
            KafkaConnectionConfig::class,
            static fn (string $name, KafkaConnectionConfig $config) => new KafkaConnection($name, $config->getOptions())
        );
    }

    public function makeConnection(string $name, ConnectionConfigInterface $config): ConnectionInterface
    {
        $configuration = \get_class($config);

        $driver = $this->drivers[$configuration]
            ?? throw DriverException::driverNotFound($configuration, array_keys($this->drivers));

        return \call_user_func($driver, $name, $config);
    }
}
