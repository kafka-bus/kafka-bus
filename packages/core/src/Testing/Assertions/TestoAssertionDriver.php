<?php

declare(strict_types=1);

namespace KafkaBus\Core\Testing\Assertions;

use Testo\Assert;

final class TestoAssertionDriver implements AssertionDriverInterface
{
    public function notEmpty(array $items, string $message): void
    {
        Assert::array($items)->notEmpty($message);
    }

    public function count(array $items, int $expected, string $message): void
    {
        Assert::count($items, $expected, $message);
    }
}
