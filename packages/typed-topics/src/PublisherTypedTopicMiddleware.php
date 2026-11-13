<?php

namespace KafkaBus\TypedTopics;

use KafkaBus\Core\Pipelines\PipelineInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Publishers\Pipelines\PublisherPipelineHandler;
use KafkaBus\Core\Publishers\Pipelines\PublisherPipelineMiddleware;

final readonly class PublisherTypedTopicMiddleware implements PublisherPipelineMiddleware
{
    /**
     * @param PipelineInterface<ProducerMessage, PublisherPipelineHandler> $pipeline
     * @return PipelineInterface<ProducerMessage, PublisherPipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        $target = $pipeline->handler()
            ->target();

        if ($target instanceof HasMessageType) {
            $pipeline->handler()
                ->withHeader(HasMessageType::HEADER_NAME, $target->getMessageType());
        }

        return $pipeline->continue();
    }
}
