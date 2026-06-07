<?php

namespace KafkaBus\Messages\Data\Casters;

use KafkaBus\Messages\Interfaces\CasterInterface;

class FloatCaster implements CasterInterface
{
    public function cast(mixed $value, string $attributeKey): float
    {
        \assert(is_numeric($value));

        return \floatval($value);
    }

    public function rollback(mixed $value, string $attributeKey): float
    {
        \assert(is_numeric($value));

        return \floatval($value);
    }
}
