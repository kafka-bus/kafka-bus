<?php

namespace KafkaBus\Core\Consumers\Router;

use KafkaBus\Core\Consumers\Pipelines\MessagePipelineMiddleware;

final readonly class RouteInfo
{
    /**
     * @param string $topicKey
     * @param callable $handler
     * @param list<MessagePipelineMiddleware> $middleware
     */
    public function __construct(
        public string $topicKey,
        public mixed $handler,
        public array $middleware = []
    ) {
    }
}
