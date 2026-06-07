<?php

namespace KafkaBus\Core\Exceptions\Connections;

use LogicException;

class ConnectionException extends LogicException
{
    /**
     * @param string $connectionName
     * @param string[] $availableConnections
     * @return self
     */
    public static function connectionNotFound(string $connectionName, array $availableConnections): self
    {
        $availableConnections = implode(', ', $availableConnections);

        return new self(
            "Connection [$connectionName] not defined." .
                " Available connections: $availableConnections"
        );
    }
}
