<?php

namespace KafkaBus\Core\Bus\Publishers\Router;

use KafkaBus\Core\Producers\Pipelines\ProducerPipelineMiddleware;

readonly class Options
{
    /**
     * @param array<string, int|bool|string|null> $additionalOptions
     * @param list<ProducerPipelineMiddleware> $middleware
     * @param int $flushTimeout
     * @param int $flushRetries
     */
    public function __construct(
        public array $additionalOptions = [],
        public array $middleware = [],
        public int   $flushTimeout = 5000,
        public int   $flushRetries = 10
    ) {
    }
}
