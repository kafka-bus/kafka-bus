<?php

namespace KafkaBus\Messages\Tests\Data\Casters;

use KafkaBus\Messages\Data\Casters\CollectionCaster;
use KafkaBus\Messages\Data\Casters\PayloadCaster;
use KafkaBus\Workbench\Data\CategoryPayload;
use Testo\Assert;
use Testo\Test;

class CollectionCasterTest
{
    #[Test]
    public function can_cast_an_array(): void
    {
        $caster = new CollectionCaster(new PayloadCaster(CategoryPayload::class));

        $rawAttributes = [
            [
                'id' => 202410192219,
                'name' => 'Тестовая категория',
            ],
        ];

        /** @var CategoryPayload[] $castedValue */
        $castedValue = $caster->cast($rawAttributes, 'category');

        Assert::array($castedValue)
            ->hasCount(1)
            ->allOf(CategoryPayload::class);

        $category = $castedValue[0];

        Assert::equals($category->id, 202410192219);
        Assert::equals($category->name, 'Тестовая категория');
    }
}
