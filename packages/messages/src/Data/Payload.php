<?php

namespace KafkaBus\Messages\Data;

use ArrayAccess;
use JsonSerializable;
use KafkaBus\Messages\Data\Casters\DateTimeCaster;
use KafkaBus\Messages\Data\Casters\NullableCaster;
use KafkaBus\Messages\Interfaces\CasterInterface;

/**
 * @implements ArrayAccess<string, mixed>
 */
class Payload implements JsonSerializable, ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * @var array<string, CasterInterface>
     */
    private array $casters = [];

    protected string $dateFormat = 'Y-m-d\TH:i:s.uP';

    /**
     * @var string[]
     */
    protected array $dates = [];

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->registerCasters();

        foreach ($attributes as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    private function registerCasters(): void
    {
        foreach ($this->dates as $attributeName) {
            $this->casters[$attributeName] = new NullableCaster(new DateTimeCaster($this->dateFormat));
        }

        $this->casters = array_merge($this->casters, $this->definitionCasters());
    }

    /**
     * @return array<string, CasterInterface>
     */
    protected function definitionCasters(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $castedAttributes = array_map(function (mixed $value, string $attributeKey) {
            return \array_key_exists($attributeKey, $this->casters)
                ? $this->casters[$attributeKey]->rollback($value, $attributeKey)
                : $value;
        }, $this->attributes, array_keys($this->attributes));

        return array_combine(array_keys($this->attributes), $castedAttributes);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[$offset] = \array_key_exists($offset, $this->casters) // @phpstan-ignore-line
            ? $this->casters[$offset]->cast($value, $offset)
            : $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    public function __unset(string $key): void
    {
        $this->offsetUnset($key);
    }

    public function __get(string $key): mixed
    {
        return $this->offsetGet($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param array<string|int, mixed> $payload
     * @return $this
     */
    public static function from(array $payload): static
    {
        return new static($payload); // @phpstan-ignore-line
    }
}
