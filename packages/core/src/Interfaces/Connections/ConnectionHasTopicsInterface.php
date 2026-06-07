<?php

namespace KafkaBus\Core\Interfaces\Connections;

use KafkaBus\Core\Interfaces\Connections\Topics\ConnectionTopicsInterface;

interface ConnectionHasTopicsInterface
{
    public function topics(): ConnectionTopicsInterface;
}
