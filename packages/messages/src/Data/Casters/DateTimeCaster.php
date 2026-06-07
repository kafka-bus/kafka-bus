<?php

namespace KafkaBus\Messages\Data\Casters;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use KafkaBus\Messages\Interfaces\CasterInterface;
use LogicException;

class DateTimeCaster implements CasterInterface
{
    public function __construct(
        protected string $format = 'Y-m-d\TH:i:s.uP',
    ) {
    }

    public function cast(mixed $value, string $attributeKey): DateTimeInterface
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        \assert(\is_string($value));

        $datetime =  DateTimeImmutable::createFromFormat($this->format, $value);

        if ($datetime === false) {
            throw new LogicException('Cannot create datetime from format: ' . $this->format);
        }

        return $datetime;
    }

    public function rollback(mixed $value, string $attributeKey): mixed
    {
        if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
            return $value->setTimezone(new DateTimeZone('UTC'))
                ->format($this->format);
        }

        return $value;
    }
}
