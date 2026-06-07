<?php

namespace KafkaBus\Core\Consumers;

readonly class ConsumerConfig
{
    /**
     * @param array<string, int|bool|string|null> $additionalOptions
     * @param bool $autoCommit
     * @param int $consumerTimeout
     */
    public function __construct(
        public array $additionalOptions = [],
        public bool $autoCommit = true,
        public int $consumerTimeout = 2000
    ) {
    }
}
