<?php

namespace KafkaBus\Core\Producers;

use KafkaBus\Core\Interfaces\Producers\ProducerInterface;

final class NullProducer implements ProducerInterface
{
    public function produce(iterable $messages): void
    {
    }
}
