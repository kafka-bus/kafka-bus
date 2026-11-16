<?php

namespace KafkaBus\Messages;

use JsonException;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Messages\Data\Payload;

class JsonMessage extends Payload implements ProducerMessageInterface
{
    public const JSON_DEPTH = 512;

    /**
     * @return string
     *
     * @throws JsonException
     */
    public function toPayload(): string
    {
        /** @var int<1, max> $depth */
        $depth = static::JSON_DEPTH;

        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR, $depth);
    }
}
