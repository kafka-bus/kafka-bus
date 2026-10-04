<?php

namespace KafkaBus\Core\Consumers\Messages;

use RdKafka\Message;

final class ConsumerMessageConverter
{
    public function fromKafka(Message $message): ConsumerMessageInterface
    {
        return new ConsumerMessage($message);
    }
}
