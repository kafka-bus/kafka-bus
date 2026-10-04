<?php

namespace KafkaBus\Core\Pipelines;

/**
 * @template-covariant TTarget = mixed
 * @template-covariant TResult = mixed
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
