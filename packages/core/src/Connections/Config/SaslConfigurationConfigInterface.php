<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Config;

interface SaslConfigurationConfigInterface
{
    /**
     * @return array<string, bool|int|string|null>
     */
    public function toOptions(): array;
}
