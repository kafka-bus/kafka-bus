<?php

namespace KafkaBus\Core\Exceptions\Consumers;

class ErrorConsumerException extends ConsumerException
{
    public function __construct(public readonly int $errorCode)
    {
        $errorMessage = rd_kafka_err2str($this->errorCode);

        parent::__construct("Kafka error: {$this->errorCode} [$errorMessage]");
    }
}
