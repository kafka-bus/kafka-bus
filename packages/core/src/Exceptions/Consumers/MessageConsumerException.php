<?php

namespace KafkaBus\Core\Exceptions\Consumers;

use RdKafka\Message;

class MessageConsumerException extends ConsumerException
{
    public function __construct(
        public readonly Message $consumerMessage
    ) {
        $errorMessage = rd_kafka_err2str($this->consumerMessage->err);

        parent::__construct("Kafka error: {$this->consumerMessage->err} [$errorMessage]");
    }
}
