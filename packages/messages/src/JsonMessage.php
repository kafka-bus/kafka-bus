<?php

namespace KafkaBus\Messages;

use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Messages\Data\Payload;

class JsonMessage extends Payload implements ProducerMessageInterface
{
    public function toPayload(): string
    {
        return (string) json_encode($this->jsonSerialize());
    }
}
