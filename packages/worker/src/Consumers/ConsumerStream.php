<?php

namespace KafkaBus\Worker\Consumers;

use KafkaBus\Core\Consumers\ConsumerInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Testing\Exceptions\KafkaMessagesEndedException;

final class ConsumerStream implements ConsumerStreamInterface
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
     * @param callable(ConsumerMessageInterface $message): void $dispatcher
     */
    public function __construct(
        protected ConsumerInterface $consumer,
        protected mixed $dispatcher
    ) {
    }

    public function listen(array $topics): void
    {
        $this->consumer->subscribe(array_column($topics, 'name'));

        do {
            try {
                $consumerMessage = $this->consumer->getMessage();

                \call_user_func($this->dispatcher, $consumerMessage);
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

        $this->consumer->unsubscribe();
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }
}
