<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Config;

interface ConnectionConfigInterface
{
    public function getOptions(): Options;
}
