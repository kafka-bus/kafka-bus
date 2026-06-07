<?php

namespace KafkaBus\Core\Consumers\Router;

use KafkaBus\Core\Consumers\Messages\NativeMessageFactory;
use KafkaBus\Core\Interfaces\Consumers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Topics\Topic;

final readonly class Route
{
    /**
     * @param Topic $topic
     * @param callable $handler
     * @param MessageFactoryInterface $messageFactory
     * @param list<MessagePipelineMiddleware> $middleware
     */
    public function __construct(
        public Topic $topic,
        public mixed $handler,
        public MessageFactoryInterface $messageFactory = new NativeMessageFactory(),
        public array $middleware = []
    ) {
    }
}
