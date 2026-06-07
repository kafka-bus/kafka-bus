<?php

namespace KafkaBus\Core\Interfaces\Consumers\Handlers;

use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use KafkaBus\Core\Topics\Topic;

interface MessageHandlerInterface
{
    /**
     * @return Topic[]
     */
    public function topics(): array;

    /**
     * @param WorkerConsumerMessageInterface $message
     * @return void
     *
     * @throws MessageConsumerNotHandledException
     */
    public function handle(WorkerConsumerMessageInterface $message): void;
}
