<?php

namespace KafkaBus\Core\Interfaces\Pipelines;

/**
 * @template-covariant THandler of PipelineHandlerInterface
 */
interface PipelineInterface
{
    /**
     * @return THandler
     */
    public function handler(): PipelineHandlerInterface;

    /**
     * @return PipelineInterface<THandler>
     */
    public function continue(): PipelineInterface;
}
