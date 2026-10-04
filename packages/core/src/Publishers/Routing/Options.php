<?php

namespace KafkaBus\Core\Publishers\Routing;

use KafkaBus\Core\Publishers\Pipelines\PublisherPipelineMiddleware;

final readonly class Options
{
    /**
     * @param array<string, int|bool|string|null> $additionalOptions
     * @param list<PublisherPipelineMiddleware> $middleware
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
