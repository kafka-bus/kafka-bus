<?php

namespace KafkaBus\Commiter\Middleware;

use KafkaBus\Core\Interfaces\Pipelines\PipelineInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Producers\Pipelines\ProducerPipelineHandler;
use KafkaBus\Core\Producers\Pipelines\ProducerPipelineMiddleware;
use KafkaBus\Commiter\Interfaces\HasIdempotency;
use KafkaBus\Commiter\Repositories\IdempotencyMessageRepository;

final readonly class ProducerIdempotencyMiddleware implements ProducerPipelineMiddleware
{
    /**
     * @param PipelineInterface<ProducerMessage, ProducerPipelineHandler> $pipeline
     * @return PipelineInterface<ProducerMessage, ProducerPipelineHandler>
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
