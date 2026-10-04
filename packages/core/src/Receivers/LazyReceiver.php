<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers;

use Closure;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;

final class LazyReceiver implements ReceiverInterface
{
    private ?ReceiverInterface $receiver = null;

    /**
     * @param Closure(): ReceiverInterface $factory
     */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    /**
     * @throws MessageConsumerNotHandledException
     */
    public function dispatch(ConsumerMessageInterface $message): void
    {
        ($this->receiver ??= ($this->factory)())
            ->dispatch($message);
    }
}
