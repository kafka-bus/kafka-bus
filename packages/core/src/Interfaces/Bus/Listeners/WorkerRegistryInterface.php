<?php

declare(strict_types=1);

namespace KafkaBus\Core\Interfaces\Bus\Listeners;

use KafkaBus\Core\Bus\Listeners\Workers\Worker;

interface WorkerRegistryInterface
{
    public function get(string $workerName): ?Worker;
}
