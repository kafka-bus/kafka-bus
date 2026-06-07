<?php

namespace KafkaBus\Core\Producers;

use KafkaBus\Core\Interfaces\Producers\ProducerInterface;
use KafkaBus\Core\Exceptions\Producers\CannotFlushProducerException;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Support\RetryRepeater;
use KafkaBus\Core\Topics\Topic;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Producer implements ProducerInterface
{
    protected ProducerTopic $producerTopic;

    public function __construct(
        protected KafkaProducer $producer,
        protected Topic $topic,
        protected RetryRepeater $retryRepeater = new RetryRepeater(),
        protected int $timeout = 2000
    ) {
        $this->producerTopic = $this->producer
            ->newTopic($this->topic->name);
    }

    public function produce(iterable $messages): void
    {
        foreach ($messages as $message) {
            $this->poll($message);
        }

        $this->flush();
    }

    private function poll(ProducerMessage $producerMessage): void
    {
        $this->producerTopic->producev(
            partition: $producerMessage->partition,
            msgflags: RD_KAFKA_MSG_F_BLOCK,
            payload: $producerMessage->payload,
            key: $producerMessage->key,
            headers: $producerMessage->headers
        );

        $this->producer->poll($this->timeout);
    }

    private function flush(): void
    {
        $this->retryRepeater
            ->execute(fn () => $this->attemptFlush());
    }

    /**
     * @throws CannotFlushProducerException
     */
    private function attemptFlush(): void
    {
        $result = $this->producer->flush($this->timeout);

        if ($result === RD_KAFKA_RESP_ERR_NO_ERROR) {
            return;
        }

        throw new CannotFlushProducerException($result);
    }
}
