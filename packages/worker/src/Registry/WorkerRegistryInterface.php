<?php

declare(strict_types=1);

namespace KafkaBus\Worker\Registry;

use KafkaBus\Worker\Worker;

interface WorkerRegistryInterface
{
    public function get(string $workerName): ?Worker;
}
