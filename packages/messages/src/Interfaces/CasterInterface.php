<?php

namespace KafkaBus\Messages\Interfaces;

interface CasterInterface
{
    public function cast(mixed $value, string $attributeKey): mixed;

    public function rollback(mixed $value, string $attributeKey): mixed;
}
