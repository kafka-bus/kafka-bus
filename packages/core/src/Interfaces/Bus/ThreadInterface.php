<?php

namespace KafkaBus\Core\Interfaces\Bus;

use KafkaBus\Core\Bus\Listeners\Listener;
use KafkaBus\Core\Bus\MessageBatch;
use KafkaBus\Core\Bus\Publishers\Router\Route;
use KafkaBus\Core\Exceptions\Listeners\ListenerException;
use KafkaBus\Core\Exceptions\Producers\RouteProducerException;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

interface ThreadInterface
{
    /**
     * @return list<Route>
     */
    public function routes(): array;

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
     * @param non-empty-string $listenerWorkerName
     * @return Listener
     *
     * @throws ListenerException
     */
    public function listener(string $listenerWorkerName): Listener;
}
