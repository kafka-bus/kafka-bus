<?php

namespace KafkaBus\Core\Producers\Messages;

use KafkaBus\Core\Topics\Topic;

readonly class ProducerMessage
{
    /**
     * @param Topic $topic
     * @param string $payload
     * @param array<string, mixed> $headers
     * @param int $partition
     * @param string|null $key
     */
    public function __construct(
        public Topic $topic,
        public string $payload,
        public array $headers = [],
        public int $partition = RD_KAFKA_PARTITION_UA,
        public ?string $key = null
    ) {
    }
}
