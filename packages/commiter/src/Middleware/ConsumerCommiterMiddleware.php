<?php

namespace KafkaBus\Commiter\Middleware;

use Exception;
use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineHandler;
use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineMiddleware;
use KafkaBus\Core\Interfaces\Pipelines\PipelineInterface;
use KafkaBus\Commiter\Interfaces\ConsumerMessageRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class ConsumerCommiterMiddleware implements ConsumerPipelineMiddleware
{
    public function __construct(
        private ConsumerMessageRepositoryInterface $repository,
        private LoggerInterface                    $logger = new NullLogger(),
        private int                                $maxAttempt = -1
    ) {
    }

    /**
     * @param PipelineInterface<ConsumerPipelineHandler> $pipeline
     * @return PipelineInterface<ConsumerPipelineHandler>
     *
     * @throws Exception
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
                ->error("Message #$attempt->key number of read attempts has been exceeded", $context);

            return $pipeline;
        }

        try {
            $pipeline->continue();

            $this->repository->commit($message);

            $this->logger
                ->debug("Message #$attempt->key successfully read", $context);

            return $pipeline;
        }
        catch (Exception $exception) {
            $this->repository->failed($message);

            throw $exception;
        }
    }
}
