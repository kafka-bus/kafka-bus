<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Topics\Consumers;

final readonly class ConsumerPartition
{
    public function __construct(
        public int $id,
        public string $topicName,
        public string $connectionName,
        public int $currentOffset = 0,
        public int $minOffset = 0,
        public int $maxOffset = 0,
    ) {
    }
}
