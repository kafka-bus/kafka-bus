<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Interfaces\Consumers\ConsumerInterface;
use KafkaBus\Core\Interfaces\Consumers\ConsumerStreamInterface;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerInterface;
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
     */
    public function __construct(
        protected ConsumerInterface $consumer,
        protected MessageHandlerInterface $messageHandler
    ) {
    }

    public function listen(): void
    {
        do {
            try {
                $consumerMessage = $this->consumer->getMessage();

                $this->messageHandler->handle($consumerMessage);
                $this->consumer->commit($consumerMessage);
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
