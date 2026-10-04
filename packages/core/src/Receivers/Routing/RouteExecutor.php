<?php

namespace KafkaBus\Core\Receivers\Routing;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Pipelines\PipelineBuilder;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineHandler;
use KafkaBus\Core\Receivers\Pipelines\ReceiverPipelineMiddleware;

final readonly class RouteExecutor
{
    /**
     * @param callable $handler
     * @param MessageFactoryInterface $factory
     * @param list<ReceiverPipelineMiddleware> $middleware
     */
    public function __construct(
        protected mixed $handler,
        protected MessageFactoryInterface $factory,
        protected array $middleware = []
    ) {
    }

    public function execute(ConsumerMessageInterface $message): void
    {
        $pipelineHandler = new ReceiverPipelineHandler(
            $message,
            $this->factory->fromKafka($message),
            $this->handler,
        );

        $pipeline = PipelineBuilder::for($pipelineHandler)
            ->middleware($this->middleware)
            ->create();

        $pipeline->start();
    }
}
