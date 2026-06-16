<?php

namespace KafkaBus\Core\Testing\Messages;

use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineHandler;
use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineMiddleware;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Interfaces\Pipelines\PipelineInterface;

/**
 * @internal
 */
final class ConsumerMiddleware implements ConsumerPipelineMiddleware
{
    /**
     * @param PipelineInterface<ConsumerMessageInterface, ConsumerPipelineHandler> $pipeline
     * @return PipelineInterface<ConsumerMessageInterface, ConsumerPipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        echo $pipeline->handler()
            ->target()
            ->topicName();

        return $pipeline->continue();

    }
}
