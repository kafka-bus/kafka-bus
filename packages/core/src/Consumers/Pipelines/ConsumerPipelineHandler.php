<?php

declare(strict_types=1);

namespace KafkaBus\Core\Consumers\Pipelines;

use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineHandlerInterface;

/**
 * @implements PipelineHandlerInterface<ConsumerMessageInterface, ConsumerMessageInterface>
 */
final class ConsumerPipelineHandler implements PipelineHandlerInterface
{
    /**
     * @param ConsumerMessageInterface $target
     * @param callable(ConsumerMessageInterface $message): void $handler
     */
    public function __construct(
        protected ConsumerMessageInterface $target,
        protected mixed $handler
    ) {
    }

    public function target(): ConsumerMessageInterface
    {
        return $this->target;
    }

    /**
     * @throws MessageConsumerNotHandledException
     */
    public function handle(): ConsumerMessageInterface
    {
        \call_user_func($this->handler, $this->target);

        return $this->target;
    }
}
