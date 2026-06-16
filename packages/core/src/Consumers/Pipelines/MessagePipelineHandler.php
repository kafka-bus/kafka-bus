<?php

declare(strict_types=1);

namespace KafkaBus\Core\Consumers\Pipelines;

use KafkaBus\Core\Interfaces\Pipelines\PipelineHandlerInterface;

/**
 * @implements PipelineHandlerInterface<mixed, mixed>
 */
final class MessagePipelineHandler implements PipelineHandlerInterface
{
    /**
     * @param callable $handler
     * @param mixed $target
     */
    public function __construct(
        protected mixed $handler,
        protected mixed $target,
    ) {
    }

    public function target(): mixed
    {
        return $this->target;
    }

    /**
     * @return mixed
     */
    public function handle(): mixed
    {
        \call_user_func($this->handler, $this->target);

        return $this->target();
    }
}
