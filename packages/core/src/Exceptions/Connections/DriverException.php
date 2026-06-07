<?php

namespace KafkaBus\Core\Exceptions\Connections;

use LogicException;

class DriverException extends LogicException
{
    /**
     * @param string $configuration
     * @param string[] $availableDrivers
     * @return self
     */
    public static function driverNotFound(string $configuration, array $availableDrivers): self
    {
        $availableDrivers = implode(', ', $availableDrivers);

        return new self("Driver for configuration [$configuration] not defined. "
            ."Available configurations: $availableDrivers");
    }
}
