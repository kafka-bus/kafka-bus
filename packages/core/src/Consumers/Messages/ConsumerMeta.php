<?php

namespace KafkaBus\Core\Consumers\Messages;

use RdKafka\Message;

final readonly class ConsumerMeta
{
    public function __construct(
        public Message $message
    ) {
    }
}
