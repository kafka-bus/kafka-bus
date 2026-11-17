<?php

namespace KafkaBus\Core\Consumers\Messages;

use JsonSerializable;
use RdKafka\Message;

final class ConsumerMessage implements ConsumerMessageInterface, JsonSerializable
{
    public function __construct(
        protected Message $message
    ) {
    }

    public function msgId(): string
    {
        return "{$this->message->partition}-{$this->message->offset}-{$this->message->topic_name}";
    }

    public function topicName(): string
    {
        return $this->message->topic_name;
    }

    public function key(): ?string
    {
        return $this->message->key == ''
            ? null
            : $this->message->key;
    }

    public function payload(): string
    {
        return $this->message->payload ?? '';
    }

    public function headers(): array
    {
        $message = $this->message;

        return $message->headers ?? [];
    }

    public function original(): Message
    {
        return $this->message;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'msg_id' => $this->msgId(),
            'topic_name' => $this->topicName(),
            'key' => $this->key(),
            'payload' => $this->payload(),
            'headers' => $this->headers(),
            'offset' => $this->message->offset,
            'partition' => $this->message->partition,
        ];
    }
}
