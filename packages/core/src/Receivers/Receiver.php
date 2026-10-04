<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Receivers\Routing\ReceiverRouter;
use Throwable;

final readonly class Receiver implements ReceiverInterface
{
    public function __construct(
        private ReceiverRouter $router = new ReceiverRouter(),
    ) {
    }

    /**
     * @throws MessageConsumerNotHandledException
     */
    public function dispatch(ConsumerMessageInterface $message): void
    {
        try {
            $this->router
                ->handle($message);
        }
        catch (Throwable $exception) {
            if ($exception instanceof MessageConsumerNotHandledException) {
                throw $exception;
            }

            throw new MessageConsumerNotHandledException($message, $exception);
        }
    }
}
