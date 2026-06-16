<?php

declare(strict_types=1);

namespace KafkaBus\Core\Pipelines;

use KafkaBus\Core\Interfaces\Pipelines\PipelineHandlerInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;

/**
 * @template TTarget
 * @template TResult
 * @template THandler of PipelineHandlerInterface<TTarget, TResult>
 */
final class PipelineBuilder
{
    /**
     * @var list<PipelineMiddlewareInterface<TResult, THandler>>
     */
    protected array $middleware = [];

    /**
     * @param THandler $pipelineHandler
     */
    public function __construct(
        protected PipelineHandlerInterface $pipelineHandler,
    ) {
    }

    /**
     * @template Target
     * @template Result
     * @template Handler of PipelineHandlerInterface<Target, Result>
     *
     * @param Handler $pipelineHandler
     * @return PipelineBuilder<Target, Result, Handler>
     */
    public static function for(PipelineHandlerInterface $pipelineHandler): PipelineBuilder
    {
        return new PipelineBuilder($pipelineHandler);
    }

    /**
     * @template TPipelineMiddleware of PipelineMiddlewareInterface<TResult, THandler>
     * @param list<TPipelineMiddleware> $middleware
     * @return $this
     */
    public function middleware(array $middleware): PipelineBuilder
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * @return Pipeline<TResult, THandler>
     */
    public function create(): Pipeline
    {
        return new Pipeline($this->pipelineHandler, $this->middleware);
    }
}
