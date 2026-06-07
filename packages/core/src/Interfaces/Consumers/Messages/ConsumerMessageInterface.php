<?php

namespace KafkaBus\Core\Interfaces\Consumers\Messages;

use RdKafka\Message;

interface ConsumerMessageInterface
{
    public function msgId(): string;

    public function topicName(): string;

    public function key(): ?string;

    public function payload(): string;

    /**
     * @return array<string, string>
     */
    public function headers(): array;

    public function original(): Message;
}
