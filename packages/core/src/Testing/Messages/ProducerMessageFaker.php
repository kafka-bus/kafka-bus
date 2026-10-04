<?php

namespace KafkaBus\Core\Testing\Messages;

use KafkaBus\Core\Producers\Messages\HasHeaders;
use KafkaBus\Core\Producers\Messages\HasPartition;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use Stringable;

final readonly class ProducerMessageFaker implements HasHeaders, HasPartition, ProducerMessageInterface
{
    /**
     * @param string $message
     * @param array<string, string|Stringable> $headers
     * @param int $partition
     */
    public function __construct(
        private string $message,
        private array $headers = [],
        private int $partition = -1,
    ) {
    }

    public function toPayload(): string
    {
        return $this->message;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPartition(): int
    {
        return $this->partition;
    }
}
