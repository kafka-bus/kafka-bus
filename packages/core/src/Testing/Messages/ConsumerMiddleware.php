<?php

namespace KafkaBus\Core\Testing\Messages;

use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineHandler;
use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineMiddleware;
use KafkaBus\Core\Interfaces\Pipelines\PipelineInterface;

/**
 * @internal
 */
final class ConsumerMiddleware implements ConsumerPipelineMiddleware
{
    /**
     * @param PipelineInterface<ConsumerPipelineHandler> $pipeline
     * @return PipelineInterface<ConsumerPipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        echo $pipeline->handler()->target()->topicName();

        return $pipeline->continue();

    }
}
