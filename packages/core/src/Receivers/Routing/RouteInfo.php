<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;

final readonly class RouteInfo
{
    /**
     * @param string $topicKey
     * @param callable $handler
     * @param list<ReceiverPipelineMiddleware> $middleware
     */
    public function __construct(
        public string $topicKey,
        public mixed $handler,
        public array $middleware = []
    ) {
    }
}
