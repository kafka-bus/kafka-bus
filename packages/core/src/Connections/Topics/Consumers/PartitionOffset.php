<?php

declare(strict_types=1);

namespace KafkaBus\Core\Connections\Topics\Consumers;

final readonly class PartitionOffset
{
    public function __construct(
        public string $topicName,
        public int $partition,
        public int $offset,
    ) {
    }
}
