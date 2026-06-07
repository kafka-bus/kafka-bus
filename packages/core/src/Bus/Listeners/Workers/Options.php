<?php

namespace KafkaBus\Core\Bus\Listeners\Workers;

use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineMiddleware;

readonly class Options
{
    /**
     * @param array<string, int|bool|string|null> $additionalOptions
     * @param list<ConsumerPipelineMiddleware> $middleware
     * @param bool $autoCommit
     * @param int $consumerTimeout
     */
    public function __construct(
        public array $additionalOptions = [],
        public array $middleware = [],
        public bool  $autoCommit = true,
        public int   $consumerTimeout = 2000,
    ) {
    }
}
