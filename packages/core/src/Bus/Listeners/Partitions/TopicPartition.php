<?php

declare(strict_types=1);

namespace KafkaBus\Core\Bus\Listeners\Partitions;

use KafkaBus\Core\Topics\Topic;

final readonly class TopicPartition
{
    public function __construct(
        public int   $id,
        public Topic $topic,
        public int   $currentOffset = -1,
        public int   $minOffset = -1,
        public int   $maxOffset = -1
    ) {
    }
}
