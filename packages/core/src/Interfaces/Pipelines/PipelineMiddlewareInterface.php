<?php

namespace KafkaBus\Core\Interfaces\Pipelines;

/**
 * @template THandler of PipelineHandlerInterface
 */
interface PipelineMiddlewareInterface
{
    /**
     * @param PipelineInterface<THandler> $pipeline
     * @return PipelineInterface<THandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface;
}
