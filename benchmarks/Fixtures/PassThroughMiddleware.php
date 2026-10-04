<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Pipelines\PipelineInterface;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineHandler;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;

final class PassThroughMiddleware implements ReceiverPipelineMiddleware
{
    /**
     * @param PipelineInterface<ConsumerMessageInterface, ReceiverPipelineHandler> $pipeline
     * @return PipelineInterface<ConsumerMessageInterface, ReceiverPipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        return $pipeline->continue();
    }
}
