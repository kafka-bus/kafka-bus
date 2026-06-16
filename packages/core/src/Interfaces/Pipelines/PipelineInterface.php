<?php

namespace KafkaBus\Core\Interfaces\Pipelines;

/**
 * @template TResult
 * @template-covariant THandler of PipelineHandlerInterface<mixed, TResult>
 */
interface PipelineInterface
{
    /**
     * @return THandler
     */
    public function handler(): PipelineHandlerInterface;

    /**
     * @return PipelineInterface<TResult, THandler>
     */
    public function continue(): PipelineInterface;

    /**
     * @return TResult
     */
    public function result(): mixed;
}
