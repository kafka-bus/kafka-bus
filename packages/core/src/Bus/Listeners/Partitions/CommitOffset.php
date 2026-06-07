<?php

declare(strict_types=1);

namespace KafkaBus\Core\Bus\Listeners\Partitions;

use KafkaBus\Core\Topics\Topic;

final readonly class CommitOffset
{
    public function __construct(
        public Topic      $topic,
        public int        $partition,
        public Offset|int $offset,
    ) {
    }
}
