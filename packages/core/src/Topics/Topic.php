<?php

namespace KafkaBus\Core\Topics;

final readonly class Topic
{
    public function __construct(
        public string $name,
        public string $key
    ) {
    }
}
