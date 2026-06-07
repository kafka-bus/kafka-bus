<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Consumers\Commiters\CommiterInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Interfaces\Consumers\ConsumerInterface;
use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Support\RetryRepeater;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;

class Consumer implements ConsumerInterface
{
    protected ConsumerMessageConverter $consumerMessageNormalizer;

    /**
     * @param KafkaConsumer $consumer
     * @param list<string> $topicNames
     * @param CommiterInterface $commiter
     * @param RetryRepeater $retryRepeater
     * @param int $consumerTimeout
     *
     * @throws Exception
     */
    public function __construct(
        protected KafkaConsumer     $consumer,
        protected array             $topicNames,
        protected CommiterInterface $commiter,
        protected RetryRepeater     $retryRepeater = new RetryRepeater(),
        protected int               $consumerTimeout = 2000
    ) {
        $this->consumerMessageNormalizer = new ConsumerMessageConverter();
        $this->consumer->subscribe($this->topicNames);
    }

    public function __destruct()
    {
        $this->consumer->unsubscribe();
        $this->consumer->close();
    }

    public function getMessage(): ConsumerMessageInterface
    {
        try {
            $message = $this->consumer
                ->consume($this->consumerTimeout);

            if ($message->err !== RD_KAFKA_RESP_ERR_NO_ERROR) {
                throw new MessageConsumerException($message);
            }

            return $this->consumerMessageNormalizer
                ->fromKafka($message);
        }
        catch (Exception $exception) {
            throw new ConsumerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
        $this->retryRepeater
            ->execute(fn () => $this->commiter->commit($consumerMessage));
    }
}
