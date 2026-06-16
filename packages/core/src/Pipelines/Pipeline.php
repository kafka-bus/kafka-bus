<?php

namespace KafkaBus\Core\Pipelines;

use LogicException;
use KafkaBus\Core\Interfaces\Pipelines\PipelineHandlerInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;

/**
 * @template TResult
 * @template THandler of PipelineHandlerInterface<mixed, TResult>
 *
 * @implements PipelineInterface<TResult, THandler>
 */
final class Pipeline implements PipelineInterface
{
    protected bool $stopped = false;
    protected bool $completed = false;

    /**
     * @var TResult|null
     */
    protected mixed $result = null;

    /**
     * @param THandler $handler
     * @param list<PipelineMiddlewareInterface<TResult, THandler>> $middlewares
     */
    public function __construct(
        protected PipelineHandlerInterface $handler,
        protected array $middlewares = [],
    ) {
    }

    /**
     * @return THandler
     */
    public function handler(): PipelineHandlerInterface
    {
        return $this->handler;
    }

    public function continue(): PipelineInterface
    {
        if ($this->completed) {
            throw new LogicException('Pipeline has already been completed.');
        }

        if (\count($this->middlewares) == 0) {
            $this->completed = true;
            $this->result = $this->handler()->handle();
            return $this;
        }

        /** @var PipelineMiddlewareInterface<TResult, THandler> $middleware */
        $middleware = array_shift($this->middlewares);

        return $middleware->handle($this);
    }

    /**
     * @return TResult|null
     */
    public function start(): mixed
    {
        $this->continue();

        return $this->result();
    }

    /**
     * @return TResult|null
     */
    public function result(): mixed
    {
        return $this->result;
    }
}
