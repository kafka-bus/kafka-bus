<?php

namespace KafkaBus\Commiter;

use DateTimeImmutable;

final readonly class Attempt
{
    /**
     * @param int $number One-based processing attempt number; incremented after a failure.
     */
    public function __construct(
        public string $key,
        public int $number = 1,
        public ?DateTimeImmutable $commitedAt = null,
    ) {
    }
}
