<?php

namespace KafkaBus\Core\Testing\Messages;

use KafkaBus\Core\Interfaces\Producers\Messages\HasHeaders;
use KafkaBus\Core\Interfaces\Producers\Messages\HasPartition;
use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;
use Stringable;

final class ProducerMessageFaker implements HasHeaders, HasPartition, ProducerMessageInterface
{
    /**
     * @param string $message
     * @param array<string, string|Stringable> $headers
     * @param int $partition
     */
    public function __construct(
        protected string $message,
        protected array $headers = [],
        protected int $partition = -1,
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
