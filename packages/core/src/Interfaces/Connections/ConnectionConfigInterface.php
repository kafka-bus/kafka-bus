<?php

declare(strict_types=1);

namespace KafkaBus\Core\Interfaces\Connections;

use KafkaBus\Core\Connections\Config\Options;

interface ConnectionConfigInterface
{
    public function getOptions(): Options;
}
