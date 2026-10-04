<?php

namespace KafkaBus\Commiter\Middleware;

use KafkaBus\Commiter\Interfaces\HasIdempotency;
use KafkaBus\Commiter\Repositories\IdempotencyMessageRepository;
use KafkaBus\Core\Pipelines\PipelineInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Publishers\Pipelines\PublisherPipelineHandler;
use KafkaBus\Core\Publishers\Pipelines\PublisherPipelineMiddleware;

final readonly class PublisherIdempotencyMiddleware implements PublisherPipelineMiddleware
{
    /**
     * @param PipelineInterface<ProducerMessage, PublisherPipelineHandler> $pipeline
     * @return PipelineInterface<ProducerMessage, PublisherPipelineHandler>
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        $message = $pipeline->handler()
            ->target();

        if ($message instanceof HasIdempotency) {
            $pipeline->handler()
                ->withHeader(IdempotencyMessageRepository::HEADER_NAME, $message->getIdempotencyKey());
        }

        return $pipeline->continue();
    }
}
