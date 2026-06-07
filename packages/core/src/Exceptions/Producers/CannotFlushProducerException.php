<?php

namespace KafkaBus\Core\Exceptions\Producers;

class CannotFlushProducerException extends ProducerException
{
    public function __construct(public readonly int $error)
    {
        $errorMessage = rd_kafka_err2str($this->error);

        parent::__construct("Kafka error: {$this->error} [$errorMessage]");
    }
}
