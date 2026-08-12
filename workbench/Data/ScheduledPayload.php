<?php

namespace KafkaBus\Workbench\Data;

use DateTimeInterface;
use KafkaBus\Messages\Data\Payload;

/**
 * @property ?DateTimeInterface $scheduledAt
 */
final class ScheduledPayload extends Payload
{
    protected string $dateFormat = 'Y-m-d';

    /**
     * @var string[]
     */
    protected array $dates = ['scheduledAt'];
}