<?php

namespace KafkaBus\Messages\Tests\Data\Casters;

use InvalidArgumentException;
use KafkaBus\Messages\Data\Casters\PayloadCaster;
use KafkaBus\Workbench\Data\CategoryPayload;
use Testo\Assert;
use Testo\Test;

class PayloadCasterTest
{
    #[Test]
    public function can_cast_an_array(): void
    {
        $caster = new PayloadCaster(CategoryPayload::class);

        $rawAttributes = [
            'id' => 202410192219,
            'name' => 'Тестовая категория',
        ];

        $castedValue = $caster->cast($rawAttributes, 'category');

        Assert::instanceOf($castedValue, CategoryPayload::class);
        Assert::equals($castedValue->id, 202410192219);
        Assert::equals($castedValue->name, 'Тестовая категория');
    }

    #[Test]
    #[Assert\ExpectException(InvalidArgumentException::class)]
    public function can_not_cast_from_array_when_value_is_not_array(): void
    {
        (new PayloadCaster(CategoryPayload::class))
            ->cast(202410192219, 'category');
    }
}
