<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Exceptions\Consumers\ConsumerException;
use RdKafka\KafkaConsumer;

interface ConsumerHandlerInterface
{
    public function error(KafkaConsumer $kafka, int $errorCode, string $errorMessage): void;

    /**
     * @return void
     *
     * @throws ConsumerException
     */
    public function throwIfError(): void;
}
