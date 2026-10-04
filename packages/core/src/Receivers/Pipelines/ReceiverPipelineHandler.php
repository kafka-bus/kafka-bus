<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers\Pipelines;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Pipelines\PipelineHandlerInterface;

/**
 * @implements PipelineHandlerInterface<ConsumerMessageInterface, ConsumerMessageInterface>
 */
final class ReceiverPipelineHandler implements PipelineHandlerInterface
{
    /**
     * @param ConsumerMessageInterface $target
     * @param mixed $formatted
     * @param callable(mixed $message): void $handler
     */
    public function __construct(
        protected ConsumerMessageInterface $target,
        protected mixed $formatted,
        protected mixed $handler
    ) {
    }

    public function target(): ConsumerMessageInterface
    {
        return $this->target;
    }

    public function formatted(): mixed
    {
        return $this->formatted;
    }

    /**
     * @throws MessageConsumerNotHandledException
     */
    public function handle(): ConsumerMessageInterface
    {
        \call_user_func($this->handler, $this->formatted);

        return $this->target;
    }
}
