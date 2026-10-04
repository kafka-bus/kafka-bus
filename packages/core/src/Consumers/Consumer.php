<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Consumers\Commiters\CommiterInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Utils\RetryRepeater;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use Throwable;

class Consumer implements ConsumerInterface
{
    protected ConsumerMessageConverter $consumerMessageNormalizer;

    /**
     * @param KafkaConsumer $consumer
     * @param CommiterInterface $commiter
     * @param RetryRepeater $retryRepeater
     * @param int $consumerTimeout
     */
    public function __construct(
        protected KafkaConsumer     $consumer,
        protected CommiterInterface $commiter,
        protected RetryRepeater     $retryRepeater = new RetryRepeater(),
        protected int               $consumerTimeout = 2000
    ) {
        $this->consumerMessageNormalizer = new ConsumerMessageConverter();
    }

    public function getConcrete(): KafkaConsumer
    {
        return $this->consumer;
    }

    public function __destruct()
    {
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

    /**
     * @param ConsumerMessageInterface $consumerMessage
     * @return void
     *
     * @throws Throwable
     */
    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
        $this->retryRepeater
            ->execute(fn () => $this->commiter->commit($consumerMessage));
    }

    /**
     * @param list<string> $topicNames
     * @return void
     *
     * @throws Exception
     */
    public function subscribe(array $topicNames): void
    {
        $this->consumer->subscribe($topicNames);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function unsubscribe(): void
    {
        $this->consumer->unsubscribe();
    }
}
