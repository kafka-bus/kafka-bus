<?php

namespace KafkaBus\Worker\Consumers;

use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

final class ConsumerStream implements ConsumerStreamInterface
{
    protected bool $forceStop = false;

    /**
     * @param ConsumerInterface $consumer
     * @param callable(ConsumerMessageInterface $message): void $dispatcher
     */
    public function __construct(
        protected ConsumerInterface $consumer,
        protected mixed $dispatcher
    ) {
    }

    public function listen(array $topics): void
    {
        $messages = $this->consumer->start($topics);

        foreach ($messages as $message) {
            \call_user_func($this->dispatcher, $message);

            $this->consumer->commit($message);

            if ($this->forceStop) {
                break;
            }
        }
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }
}
