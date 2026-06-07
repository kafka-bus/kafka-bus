<?php

namespace KafkaBus\Messages\Data\Casters;

use KafkaBus\Messages\Interfaces\CasterInterface;

class IntegerCaster implements CasterInterface
{
    public function cast(mixed $value, string $attributeKey): int
    {
        \assert(is_numeric($value));

        return \intval($value);
    }

    public function rollback(mixed $value, string $attributeKey): int
    {
        \assert(is_numeric($value));

        return \intval($value);
    }
}
