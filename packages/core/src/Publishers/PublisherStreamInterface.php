<?php

namespace KafkaBus\Core\Publishers;

use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;

/**
 * @template TMessage of ProducerMessageInterface = mixed
 */
interface PublisherStreamInterface
{
    /**
     * @param iterable<ProducerMessageInterface> $messages
     */
    public function handle(iterable $messages): void;
}
