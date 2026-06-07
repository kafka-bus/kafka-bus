<?php

namespace KafkaBus\Core\Interfaces\Producers\Messages;

interface ProducerMessageInterface
{
    public function toPayload(): string;
}
