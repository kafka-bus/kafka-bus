<?php

namespace KafkaBus\Core\Testing\Consumers;

use RdKafka\Message;
use JsonException;

class MessageFactory
{
    /**
     * @var array<string, string>
     */
    protected array $headers = [];

    protected string|null $key = null;

    protected string $topicKey = 'test';

    protected int $partition = 0;

    protected int $offset = 0;

    public static function for(): MessageFactory
    {
        return new self();
    }

    /**
     * @param array<string, string> $headers
     * @return static
     */
    public function withHeaders(array $headers): static
    {
        return $this->immutableSet('headers', $headers);
    }

    public function withKey(?string $key): static
    {
        return $this->immutableSet('key', $key);
    }

    public function withTopicKey(?string $topicKey): static
    {
        return $this->immutableSet('topicKey', $topicKey);
    }

    public function withPartition(int $partition): static
    {
        return $this->immutableSet('partition', $partition);
    }

    public function withOffset(int $offset): static
    {
        return $this->immutableSet('offset', $offset);
    }

    private function immutableSet(string $field, mixed $value): static
    {
        $clone = clone $this;
        $clone->$field = $value;

        return $clone;
    }

    public function make(string $payload): Message
    {
        $message = new Message();
        $message->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $message->payload = $payload;
        $message->headers = $this->headers;
        $message->key = $this->key ?: '';
        $message->partition = $this->partition;
        $message->offset = $this->offset;
        $message->topic_name = $this->topicKey;

        return $message;
    }

    /**
     * @param array<string|int, mixed> $attributes
     * @return Message
     *
     * @throws JsonException
     */

    public function fromArray(array $attributes): Message
    {
        return $this->make(json_encode($attributes, JSON_THROW_ON_ERROR));
    }
}
