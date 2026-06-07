<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections;

final readonly class Config
{
    /**
     * @param string $name
     * @param string $driver
     * @param array<string, int|bool|string|null> $options
     */
    public function __construct(
        public string $name,
        public string $driver,
        public array $options
    ) {
    }
}
