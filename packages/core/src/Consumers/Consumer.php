<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Consumers\Commiters\CommiterInterface;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerException;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Utils\RetryRepeater;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use Throwable;

class Consumer implements ConsumerInterface
{
    protected ConsumerMessageConverter $consumerMessageNormalizer;

    private bool $subscribed = false;

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

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        $this->unsubscribe();
        $this->consumer->close();
    }

    /**
     * @param list<Topic> $topics
     * @return iterable<ConsumerMessageInterface>
     *
     * @throws ConsumerException
     * @throws Exception
     * @throws MessageConsumerException
     */
    public function start(array $topics): iterable
    {
        $this->subscribe(array_column($topics, 'name'));

        do {
            try {
                $message = $this->consumer
                    ->consume($this->consumerTimeout);

                // @phpstan-ignore-next-line support rdkafka 7.x
                if ($message === null) {
                    continue;
                }

                switch ($message->err) {
                    case RD_KAFKA_RESP_ERR_NO_ERROR:
                        yield $this->consumerMessageNormalizer->fromKafka($message);
                        break;
                    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    case RD_KAFKA_RESP_ERR__TIMED_OUT:
                        break;
                    default:
                        throw new MessageConsumerException($message);
                }
            }
            catch (Exception $exception) {
                throw new ConsumerException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
        while (true);
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
        $this->unsubscribe();

        $this->consumer->subscribe($topicNames);
        $this->subscribed = true;
    }

    /**
     * @return void

     * @throws Exception
     */
    private function unsubscribe(): void
    {
        if ($this->subscribed) {
            $this->consumer->unsubscribe();
            $this->subscribed = false;
        }
    }
}
