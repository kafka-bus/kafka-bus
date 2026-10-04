<?php

namespace KafkaBus\Core\Publishers;

use KafkaBus\Core\Pipelines\PipelineBuilder;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Producers\ProducerInterface;
use KafkaBus\Core\Publishers\Pipelines\PublisherPipelineHandler;
use KafkaBus\Core\Publishers\Routing\Route;

/**
 * @template TMessage of ProducerMessageInterface
 * @implements PublisherStreamInterface<TMessage>
 */
final readonly class PublisherStream implements PublisherStreamInterface
{
    /**
     * @param Route<TMessage> $route
     * @param ProducerInterface $producer
     */
    public function __construct(
        protected Route $route,
        protected ProducerInterface $producer,
    ) {
    }

    public function handle(iterable $messages): void
    {
        $this->producer
            ->produce($this->prepareMessages($messages));
    }

    /**
     * @param iterable<ProducerMessageInterface> $messages
     * @return iterable<ProducerMessage>
     */
    private function prepareMessages(iterable $messages): iterable
    {
        foreach ($messages as $message) {
            $producerHandler = new PublisherPipelineHandler($message, $this->route->topic);
            $producerMessage = PipelineBuilder::for($producerHandler)
                ->middleware($this->route->options->middleware)
                ->create()
                ->start();

            if (!\is_null($producerMessage)) {
                yield $producerMessage;
            }
        }
    }
}
