<?php

declare(strict_types=1);

namespace KafkaBus\Core\Consumers\Pipelines;

use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\WorkerConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineHandlerInterface;

/**
 * @implements PipelineHandlerInterface<WorkerConsumerMessageInterface, WorkerConsumerMessageInterface>
 */
final class ConsumerPipelineHandler implements PipelineHandlerInterface
{
    public function __construct(
        protected WorkerConsumerMessageInterface $target,
        protected MessageHandlerInterface $messageHandler
    ) {
    }

    public function target(): WorkerConsumerMessageInterface
    {
        return $this->target;
    }

    /**
     * @throws MessageConsumerNotHandledException
     */
    public function handle(): WorkerConsumerMessageInterface
    {
        $this->messageHandler
            ->handle($this->target);

        return $this->target;
    }
}
