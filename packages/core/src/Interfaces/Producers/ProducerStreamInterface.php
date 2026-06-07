<?php

namespace KafkaBus\Core\Interfaces\Producers;

use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;

/**
 * @template TMessage of ProducerMessageInterface = mixed
 */
interface ProducerStreamInterface
{
    /**
     * @param iterable<TMessage> $messages
     */
    public function handle(iterable $messages): void;
}
