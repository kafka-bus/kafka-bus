<?php

namespace KafkaBus\Commiter;

use DateTimeImmutable;

final readonly class Attempt
{
    public function __construct(
        public string $key,
        public int $number = 1,
        public ?DateTimeImmutable $commitedAt = null,
    ) {
    }
}
