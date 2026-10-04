<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Config;

final readonly class NullConnectionConfig implements ConnectionConfigInterface
{
    public function getOptions(): Options
    {
        return new Options();
    }
}
