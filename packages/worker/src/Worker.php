<?php

declare(strict_types=1);

namespace KafkaBus\Worker;

use KafkaBus\Core\Topics\Topic;

readonly class Worker
{
    /**
     * @param string $name
     * @param list<Topic> $topics
     * @param Options $options
     */
    public function __construct(
        public string $name,
        public array  $topics,
        public Options $options = new Options(),
    ) {
    }
}
