<?php

declare(strict_types=1);

namespace KafkaBus\Core\Receivers\Pipelines;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Pipelines\PipelineHandlerInterface;
use KafkaBus\Core\Receivers\Routing\HandlerExecutor;

/**
 * @implements PipelineHandlerInterface<ConsumerMessageInterface, ConsumerMessageInterface>
 */
final readonly class ReceiverPipelineHandler implements PipelineHandlerInterface
{
    private mixed $formatted;

    public function __construct(
        private ConsumerMessageInterface $target,
        private HandlerExecutor $executor,
    ) {
        $this->formatted = $this->executor->fromKafka($this->target);
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
        $this->executor->execute($this->formatted, $this->target);

        return $this->target;
    }
}
