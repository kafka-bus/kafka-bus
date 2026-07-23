<?php

namespace KafkaBus\Messages\Tests\Data\Casters;

use InvalidArgumentException;
use KafkaBus\Messages\Data\Casters\EnumCaster;
use KafkaBus\Workbench\Data\CategoryStatusEnum;
use Testo\Assert;
use Testo\Test;

class EnumCasterTest
{
    #[Test]
    public function can_cast_a_backing_value(): void
    {
        $caster = new EnumCaster(CategoryStatusEnum::class);

        $castedValue = $caster->cast('archived', 'status');

        Assert::same($castedValue, CategoryStatusEnum::Archived);
    }

    #[Test]
    public function can_cast_an_already_casted_value(): void
    {
        $caster = new EnumCaster(CategoryStatusEnum::class);

        $castedValue = $caster->cast(CategoryStatusEnum::Active, 'status');

        Assert::same($castedValue, CategoryStatusEnum::Active);
    }

    #[Test]
    #[Assert\ExpectException(InvalidArgumentException::class)]
    public function can_not_cast_value_that_is_not_a_string_or_an_integer(): void
    {
        (new EnumCaster(CategoryStatusEnum::class))
            ->cast([], 'status');
    }

    #[Test]
    public function can_rollback_an_enum_instance(): void
    {
        $caster = new EnumCaster(CategoryStatusEnum::class);

        Assert::same($caster->rollback(CategoryStatusEnum::Archived, 'status'), 'archived');
    }

    #[Test]
    public function rollback_returns_value_unchanged_when_it_is_not_an_enum_instance(): void
    {
        $caster = new EnumCaster(CategoryStatusEnum::class);

        Assert::null($caster->rollback(null, 'status'));
    }
}
