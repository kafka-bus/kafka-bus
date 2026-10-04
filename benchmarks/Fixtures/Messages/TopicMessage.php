<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures\Messages;

use KafkaBus\Commiter\Interfaces\HasIdempotency;
use KafkaBus\Core\Producers\Messages\HasHeaders;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;

/**
 * База для TopicMessage0..9: маршрут публикации выбирается по классу сообщения, поэтому на каждый топик свой класс.
 */
abstract readonly class TopicMessage implements HasHeaders, HasIdempotency, ProducerMessageInterface
{
    public function __construct(private string $payload = 'payload', private string $key = 'idempotency-key')
    {
    }

    public function toPayload(): string
    {
        return $this->payload;
    }

    public function getHeaders(): array
    {
        return ['source' => 'benchmark'];
    }

    public function getIdempotencyKey(): string
    {
        return $this->key;
    }
}
