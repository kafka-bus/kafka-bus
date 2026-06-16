<?php

namespace KafkaBus\Core\Interfaces\Pipelines;

/**
 * @template TResult
 * @template THandler of PipelineHandlerInterface<mixed, TResult>
 */
interface PipelineMiddlewareInterface
{
    /**
     * @param PipelineInterface<TResult, THandler> $pipeline
     * @return PipelineInterface<TResult, THandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface;
}
