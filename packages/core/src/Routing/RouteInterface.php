<?php

declare(strict_types=1);

namespace KafkaBus\Core\Routing;

interface RouteInterface
{
    public function key(): string;
}
