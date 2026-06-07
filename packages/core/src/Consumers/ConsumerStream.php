<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Consumers\Messages\WorkerConsumerMessage;
use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineHandler;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Interfaces\Consumers\ConsumerInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerStreamInterface;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerInterface;
use KafkaBus\Core\Pipelines\PipelineBuilder;
use KafkaBus\Core\Testing\Exceptions\KafkaMessagesEndedException;

class ConsumerStream implements ConsumerStreamInterface
{
    protected bool $forceStop = false;

    private const IGNORABLE_CONSUMER_ERRORS = [
        RD_KAFKA_RESP_ERR__PARTITION_EOF,
        RD_KAFKA_RESP_ERR__TRANSPORT,
        RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT,
        RD_KAFKA_RESP_ERR__TIMED_OUT,
    ];

    /**
     * @param ConsumerInterface $consumer
     * @param MessageHandlerInterface $messageHandler
     * @param Worker $worker
     */
    public function __construct(
        protected ConsumerInterface $consumer,
        protected MessageHandlerInterface $messageHandler,
        protected Worker $worker,
    ) {
    }

    public function listen(): void
    {
        do {
            try {
                $consumerMessage = $this->consumer
                    ->getMessage();

                $workerMessage = new WorkerConsumerMessage($this->worker->name, $consumerMessage);
                $pipelineHandler = new ConsumerPipelineHandler($workerMessage, $this->messageHandler);

                $pipeline = PipelineBuilder::for($pipelineHandler)
                    ->middleware($this->worker->options->middleware)
                    ->create();

                $pipeline->start();

                $this->consumer->commit($workerMessage);
            }
            catch (MessageConsumerException $exception) {
                if (! \in_array($exception->consumerMessage->err, self::IGNORABLE_CONSUMER_ERRORS, true)) {
                    throw $exception;
                }
            }
            catch (KafkaMessagesEndedException) {
                return;
            }
        }
        while (! $this->forceStop);
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }
}
