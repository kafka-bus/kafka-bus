<?php

namespace KafkaBus\Core\Consumers;

use KafkaBus\Core\Connections\Kafka\KafkaLoggerInterface;
use Psr\Log\LoggerInterface;
use RdKafka\KafkaConsumer;

final readonly class LoggerConsumerHandler implements ConsumerHandlerInterface, KafkaLoggerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ConsumerHandlerInterface $consumerHandler,
    ) {
    }

    public function error(KafkaConsumer $kafka, int $errorCode, string $errorMessage): void
    {
        $this->logger->info(\sprintf('Kafka Error [%s] %s', $errorCode, $errorMessage));
        $this->consumerHandler->error($kafka, $errorCode, $errorMessage);
    }

    public function logging(object $kafka, int $level, string $facility, string $message): void
    {
        $this->logger->log($level, $message);
    }

    public function throwIfError(): void
    {
        $this->consumerHandler->throwIfError();
    }
}
