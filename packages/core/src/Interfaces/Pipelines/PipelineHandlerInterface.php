<?php

namespace KafkaBus\Core\Interfaces\Pipelines;

/**
 * @template-covariant TTarget
 * @template-covariant TResult
 */
interface PipelineHandlerInterface
{
    /**
     * @return TTarget
     */
    public function target(): mixed;

    /**
     * @return TResult
     */
    public function handle(): mixed;
}
