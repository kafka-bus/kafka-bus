<?php

namespace KafkaBus\Core\Consumers\Messages;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use RdKafka\Message;

final class ConsumerMessage implements ConsumerMessageInterface
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
        return $this->message->payload;
    }

    public function headers(): array
    {
        return $this->message->headers;
    }

    public function original(): Message
    {
        return $this->message;
    }
}
