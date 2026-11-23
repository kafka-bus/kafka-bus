<?php

namespace KafkaBus\Core\Connections\Kafka;

interface KafkaLoggerInterface
{
    public function logging(object $kafka, int $level, string $facility, string $message): void;
}
