<?php

namespace KafkaBus\Core\Testing\Messages;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Pipelines\PipelineInterface;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineHandler;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;

/**
 * @internal
 */
final class ReceiverMiddleware implements ReceiverPipelineMiddleware
{
    /**
     * @param PipelineInterface<ConsumerMessageInterface, ReceiverPipelineHandler> $pipeline
     * @return PipelineInterface<ConsumerMessageInterface, ReceiverPipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        echo $pipeline->handler()
            ->target()
            ->topicName();

        return $pipeline->continue();

    }
}
