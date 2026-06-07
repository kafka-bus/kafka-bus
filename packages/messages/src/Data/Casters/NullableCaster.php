<?php

namespace KafkaBus\Messages\Data\Casters;

use KafkaBus\Messages\Interfaces\CasterInterface;

class NullableCaster implements CasterInterface
{
    public function __construct(
        protected CasterInterface $caster
    ) {
    }

    public function cast(mixed $value, string $attributeKey): mixed
    {
        if (\is_null($value)) {
            return null;
        }

        return $this->caster
            ->cast($value, $attributeKey);
    }

    public function rollback(mixed $value, string $attributeKey): mixed
    {
        if (\is_null($value)) {
            return null;
        }

        return $this->caster
            ->rollback($value, $attributeKey);
    }
}
