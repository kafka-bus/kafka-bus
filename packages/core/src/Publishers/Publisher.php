<?php

namespace KafkaBus\Core\Publishers;

use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\Routing\PublisherRouter;
use KafkaBus\Core\Publishers\Routing\Route;

class Publisher
{
    public function __construct(
        protected PublisherRouter $router
    ) {
    }

    /**
     * @return list<Route>
     */
    public function routes(): array
    {
        return $this->router
            ->routes();
    }

    /**
     * @template TMessage of ProducerMessageInterface
     *
     * @param MessageBatch<TMessage> $messageBatch
     * @return void
     */
    public function publish(MessageBatch $messageBatch): void
    {
        $this->router
            ->publish($messageBatch);
    }
}
