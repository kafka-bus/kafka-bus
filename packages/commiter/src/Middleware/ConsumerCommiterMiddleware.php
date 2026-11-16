<?php

namespace KafkaBus\Commiter\Middleware;

use KafkaBus\Commiter\Interfaces\ConsumerMessageRepositoryInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Pipelines\PipelineInterface;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineHandler;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final readonly class ConsumerCommiterMiddleware implements ReceiverPipelineMiddleware
{
    public function __construct(
        private ConsumerMessageRepositoryInterface $repository,
        private LoggerInterface                    $logger = new NullLogger(),
        private int                                $maxAttempt = -1
    ) {
    }

    /**
     * @param PipelineInterface<ConsumerMessageInterface, ReceiverPipelineHandler> $pipeline
     * @return PipelineInterface<ConsumerMessageInterface, ReceiverPipelineHandler>
     *
     * @throws Throwable
     */
    public function handle(PipelineInterface $pipeline): PipelineInterface
    {
        $message = $pipeline->handler()
            ->target();

        $attempt = $this->repository->attempt($message);

        $context = [
            'msg_id' => $attempt->key,
            'topic_name' => $message->topicName(),
            'partition' => $message->original()->partition,
            'offset' => $message->original()->offset,
        ];

        if (!\is_null($attempt->commitedAt)) {
            $this->logger
                ->warning("Message #$attempt->key already read", $context);

            return $pipeline;
        }

        if ($this->maxAttempt > 0 && $attempt->number > $this->maxAttempt) {
            $this->logger
                ->error('Maximum number of message read attempts exceeded', $context);

            return $pipeline;
        }

        try {
            $pipeline->continue();

            $this->repository->commit($message);

            $this->logger
                ->debug("Message #$attempt->key successfully read", $context);

            return $pipeline;
        }
        catch (Throwable $exception) {
            $this->repository->failed($message);

            throw $exception;
        }
    }
}
