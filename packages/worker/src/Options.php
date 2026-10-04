<?php

declare(strict_types=1);

namespace KafkaBus\Worker;

readonly class Options
{
    /**
     * @param array<string, int|bool|string|null> $additionalOptions
     * @param bool $autoCommit
     * @param int $consumerTimeout
     */
    public function __construct(
        public array $additionalOptions = [],
        public bool  $autoCommit = true,
        public int   $consumerTimeout = 2000,
    ) {
    }
}
