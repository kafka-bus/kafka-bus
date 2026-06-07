<?php

namespace KafkaBus\Core\Producers;

readonly class ProducerConfig
{
    /**
     * @param array<string, int|bool|string|null> $additionalOptions
     * @param int $flushTimeout
     * @param int $flushRetries
     */
    public function __construct(
        public array $additionalOptions = [],
        public int $flushTimeout = 5000,
        public int $flushRetries = 10
    ) {
    }
}
