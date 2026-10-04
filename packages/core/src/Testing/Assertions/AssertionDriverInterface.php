<?php

declare(strict_types=1);

namespace KafkaBus\Core\Testing\Assertions;

interface AssertionDriverInterface
{
    /**
     * @param list<mixed> $items
     */
    public function notEmpty(array $items, string $message): void;

    /**
     * @param list<mixed> $items
     */
    public function count(array $items, int $expected, string $message): void;
}
