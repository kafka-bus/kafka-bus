<?php

namespace KafkaBus\Core\Interfaces\Consumers;

use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;

interface ConsumerStreamFactoryInterface
{
    public function create(ConnectionInterface $connection, Worker $worker): ConsumerStreamInterface;
}
