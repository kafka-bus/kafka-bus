<?php

namespace KafkaBus\Core\Producers;

final class NullProducer implements ProducerInterface
{
    public function produce(iterable $messages): void
    {
    }
}
