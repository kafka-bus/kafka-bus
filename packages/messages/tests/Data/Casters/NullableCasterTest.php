<?php

namespace KafkaBus\Messages\Tests\Data\Casters;

use KafkaBus\Messages\Data\Casters\IntegerCaster;
use KafkaBus\Messages\Data\Casters\NullableCaster;
use Testo\Assert;
use Testo\Data\DataSet;
use Testo\Test;

class NullableCasterTest
{
    #[Test]
    #[DataSet([null], 'value is null')]
    #[DataSet([202410212239], 'value is not null')]
    public function can_cast_value(int|null $expectedValue)
    {
        $caster = new NullableCaster(new IntegerCaster());

        Assert::equals($caster->cast($expectedValue, 'test'), $expectedValue);
    }
}
