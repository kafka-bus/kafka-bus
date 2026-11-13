<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;
use KafkaBus\Core\Routing\RouteInterface;
use KafkaBus\Core\Topics\Topic;

final readonly class Route implements RouteInterface
{
    /**
     * @param Topic $topic
     * @param HandlerExecutor $handler
     * @param list<ReceiverPipelineMiddleware> $middleware
     */
    public function __construct(
        public Topic $topic,
        public HandlerExecutor $handler,
        public array $middleware = []
    ) {
    }

    public function key(): string
    {
        return $this->topic->name;
    }
}
