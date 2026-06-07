<?php

namespace KafkaBus\Core\Consumers\Handlers;

use KafkaBus\Core\Consumers\Router\ConsumerRouter;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use Throwable;

class MessageHandler implements MessageHandlerInterface
{
    /**
     * @param ConsumerRouter $consumerRouter
     */
    public function __construct(
        protected ConsumerRouter $consumerRouter,
    ) {
    }

    public function topics(): array
    {
        return $this->consumerRouter->topics();
    }

    public function handle(WorkerConsumerMessageInterface $message): void
    {
        try {
            $this->consumerRouter
                ->handle($message);
        }
        catch (Throwable $exception) {
            throw new MessageConsumerNotHandledException($message, $exception);
        }
    }
}
