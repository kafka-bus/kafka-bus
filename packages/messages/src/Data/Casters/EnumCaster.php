<?php

namespace KafkaBus\Messages\Data\Casters;

use BackedEnum;
use InvalidArgumentException;
use KafkaBus\Messages\Interfaces\CasterInterface;
use Webmozart\Assert\Assert;

/**
 * @template TEnum of BackedEnum
 */
class EnumCaster implements CasterInterface
{
    /**
     * @param class-string<TEnum> $enumClass
     * @param TEnum|null $default
     */
    public function __construct(
        protected string $enumClass,
        protected BackedEnum|null $default = null,
    ) {
        Assert::classExists($this->enumClass);
        Assert::isAOf($this->enumClass, BackedEnum::class);
    }

    /**
     * @param mixed $value
     * @param string $attributeKey
     * @return TEnum|null
     */
    public function cast(mixed $value, string $attributeKey): BackedEnum|null
    {
        if ($value instanceof $this->enumClass) {
            return $value;
        }

        if (!\is_string($value) && !\is_int($value)) {
            throw new InvalidArgumentException("Поле $attributeKey должно быть строкой или числом");
        }

        return ($this->enumClass)::tryFrom($value) ?? $this->default;
    }

    public function rollback(mixed $value, string $attributeKey): mixed
    {
        return $value instanceof BackedEnum
            ? $value->value
            : $value;
    }
}
