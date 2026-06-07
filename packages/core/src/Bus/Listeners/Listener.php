<?php

namespace KafkaBus\Core\Bus\Listeners;

use LogicException;
use KafkaBus\Core\Bus\Listeners\Partitions\Partitions;
use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Interfaces\Bus\Listeners\ListenerInterface;
use KafkaBus\Core\Interfaces\Bus\Listeners\PartitionsInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionHasTopicsInterface;
use KafkaBus\Core\Interfaces\Connections\ConnectionInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerStreamInterface;

class Listener implements ListenerInterface
{
    public function __construct(
        protected Worker                  $worker,
        protected ConnectionInterface     $connection,
        protected ConsumerStreamInterface $consumerStream
    ) {
    }

    /**
     * @return PartitionsInterface
     */
    public function partitions(): PartitionsInterface
    {
        if (!$this->connection instanceof ConnectionHasTopicsInterface) {
            throw new LogicException("Connection {$this->connection->getName()} not supported topics");
        }

        $consumerConfig = new ConsumerConfig($this->worker->options->additionalOptions);
        $consumerTopics = $this->connection->topics()->consume($consumerConfig);

        return new Partitions($this->worker, $consumerTopics);
    }

    public function forceStop(): void
    {
        $this->consumerStream
            ->forceStop();
    }

    /**
     * @throws ConsumerException
     * @throws MessageConsumerNotHandledException
     * @throws MessageConsumerException
     */
    public function listen(): void
    {
        $this->consumerStream
            ->listen();
    }
}
