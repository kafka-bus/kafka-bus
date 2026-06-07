<?php

namespace KafkaBus\Messages\Data\Casters;

use KafkaBus\Messages\Interfaces\CasterInterface;
use Webmozart\Assert\Assert;

class CollectionCaster implements CasterInterface
{
    public function __construct(
        protected CasterInterface $caster
    ) {
    }

    /**
     * @param mixed $value
     * @param string $attributeKey
     * @return array<int, mixed>
     */
    public function cast(mixed $value, string $attributeKey): array
    {
        if (\is_null($value)) {
            return [];
        }

        Assert::isArray($value, "Поле $attributeKey должно быть массивом");

        return array_map(function (mixed $value, mixed $index) use ($attributeKey) {
            return $this->caster
                ->cast($value, "$attributeKey.$index");
        }, $value, array_keys($value));
    }

    /**
     * @param mixed $value
     * @param string $attributeKey
     * @return string|int|array<int, mixed>|null
     */
    public function rollback(mixed $value, string $attributeKey): string|int|array|null
    {
        if (\is_null($value)) {
            return [];
        }

        Assert::isArray($value, "Поле $attributeKey должно быть массивом");

        return array_map(function (mixed $value, mixed $index) use ($attributeKey) {
            return $this->caster
                ->rollback($value, "$attributeKey.$index");
        }, $value, array_keys($value));
    }
}
