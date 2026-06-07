<?php

namespace KafkaBus\Core\Consumers\Messages;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use RdKafka\Message;

final class ConsumerMessageConverter
{
    public function fromKafka(Message $message): ConsumerMessageInterface
    {
        return new ConsumerMessage($message);
    }
}
