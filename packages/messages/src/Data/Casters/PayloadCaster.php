<?php

namespace KafkaBus\Messages\Data\Casters;

use KafkaBus\Messages\Data\Payload;
use KafkaBus\Messages\Interfaces\CasterInterface;
use Webmozart\Assert\Assert;

/**
 * @template TObject of Payload
 */
class PayloadCaster implements CasterInterface
{
    /**
     * @param class-string<TObject> $payloadClass
     */
    public function __construct(
        protected string $payloadClass,
    ) {
        Assert::classExists($this->payloadClass);
        Assert::isAOf($this->payloadClass, Payload::class);
    }

    /**
     * @param mixed $value
     * @param string $attributeKey
     * @return TObject
     */
    public function cast(mixed $value, string $attributeKey): mixed
    {
        if ($value instanceof $this->payloadClass) {
            return $value;
        }

        Assert::isArray($value, "Поле $attributeKey должно быть массивом");

        return ($this->payloadClass)::from($value);
    }

    public function rollback(mixed $value, string $attributeKey): mixed
    {
        return $value instanceof Payload
            ? $value->jsonSerialize()
            : $value;
    }
}
