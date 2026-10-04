<?php

declare(strict_types=1);

namespace KafkaBus\Core\Testing\Assertions;

use PHPUnit\Framework\Assert;

final class PHPUnitAssertionDriver implements AssertionDriverInterface
{
    public function notEmpty(array $items, string $message): void
    {
        Assert::assertNotEmpty($items, $message);
    }

    public function count(array $items, int $expected, string $message): void
    {
        Assert::assertCount($expected, $items, $message);
    }
}
