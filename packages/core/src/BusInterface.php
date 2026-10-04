<?php

namespace KafkaBus\Core;

use KafkaBus\Core\Connections\ConnectionInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Exceptions\Producers\RouteProducerException;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\MessageBatch;

interface BusInterface
{
    public function connection(): ConnectionInterface;

    /**
     * @param ProducerMessageInterface $message
     * @return void
     *
     * @throws RouteProducerException
     */
    public function publish(ProducerMessageInterface $message): void;

    /**
     * @template TMessage of ProducerMessageInterface
     * @param MessageBatch<TMessage> $messageBatch
     * @return void
     *
     * @throws RouteProducerException
     */
    public function publishBatch(MessageBatch $messageBatch): void;

    /**
     * @param ConsumerMessageInterface $message
     * @return void
     *
     * @throws MessageConsumerNotHandledException
     */
    public function dispatch(ConsumerMessageInterface $message): void;
}
