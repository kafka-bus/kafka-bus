<?php

namespace KafkaBus\Core\Producers;

use KafkaBus\Core\Producers\Messages\ProducerMessage;

interface ProducerInterface
{
    /**
     * @param iterable<ProducerMessage> $messages
     */
    public function produce(iterable $messages): void;
}
