<?php

namespace KafkaBus\Core\Consumers\Handlers;

use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Consumers\Pipelines\ConsumerPipelineHandler;
use KafkaBus\Core\Consumers\Router\ConsumerRouter;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Interfaces\Consumers\Handlers\MessageHandlerInterface;
use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Pipelines\PipelineBuilder;
use Throwable;

final class MessageHandler implements MessageHandlerInterface
{
    private ConsumerRouter $consumerRouter;

    public function __construct(private readonly Worker $worker)
    {
        $this->consumerRouter = new ConsumerRouter($worker->routes);
    }

    public function topics(): array
    {
        return $this->consumerRouter->topics();
    }

    public function handle(ConsumerMessageInterface $message): void
    {
        try {
            $pipelineHandler = new ConsumerPipelineHandler($message, $this->consumerRouter->handle(...));

            $pipeline = PipelineBuilder::for($pipelineHandler)
                ->middleware($this->worker->options->middleware)
                ->create();

            $pipeline->start();
        }
        catch (Throwable $exception) {
            if ($exception instanceof MessageConsumerNotHandledException) {
                throw $exception;
            }

            throw new MessageConsumerNotHandledException($message, $exception);
        }
    }
}
