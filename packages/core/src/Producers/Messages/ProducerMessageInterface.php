<?php

namespace KafkaBus\Core\Producers\Messages;

interface ProducerMessageInterface
{
    public function toPayload(): string;
}
