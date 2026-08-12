<?php

namespace KafkaBus\Workbench\Data;

use DateTimeInterface;
use KafkaBus\Messages\Data\Payload;

/**
 * @property string $name
 * @property ?DateTimeInterface $occurredAt
 */
final class EventPayload extends Payload
{
    /**
     * @var string[]
     */
    protected array $dates = ['occurredAt'];
}