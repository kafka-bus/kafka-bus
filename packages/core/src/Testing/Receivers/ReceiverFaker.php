<?php

declare(strict_types=1);

namespace KafkaBus\Core\Testing\Receivers;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\ReceiverInterface;

final class ReceiverFaker implements ReceiverInterface
{
    /**
     * @var array<string, list<ConsumerMessageInterface>>
     */
    public array $dispatchedMessages = [];

    public function __construct(
        private readonly ReceiverInterface $receiver,
    ) {
    }

    public function dispatch(ConsumerMessageInterface $message): void
    {
        $this->receiver->dispatch($message);

        $this->dispatchedMessages[$message->topicName()][] = $message;
    }
}
