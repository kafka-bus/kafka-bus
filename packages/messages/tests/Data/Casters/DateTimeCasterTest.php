<?php

namespace KafkaBus\Messages\Tests\Data\Casters;

use KafkaBus\Messages\Data\Casters\DateTimeCaster;
use Testo\Assert;
use Testo\Test;
use DateTime;

class DateTimeCasterTest
{
    #[Test]
    public function cast_date_time(): void
    {
        $caster = new DateTimeCaster();

        /** @var DateTime $datetime */
        $datetime = $caster->cast('2020-01-01T06:00:00.000000+03:00', 'updated_at');
        $rollback = $caster->rollback($datetime, 'updated_at');

        Assert::equals($datetime->getTimezone()->getName(), '+03:00');
        Assert::equals($rollback, '2020-01-01T03:00:00.000000+00:00');
    }
}
