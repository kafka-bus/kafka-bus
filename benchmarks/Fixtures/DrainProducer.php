<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks\Fixtures;

use KafkaBus\Core\Producers\ProducerInterface;

/**
 * Продюсер-заглушка: вычитывает поток (чтобы отработали middleware), но ничего не хранит.
 */
final class DrainProducer implements ProducerInterface
{
    public int $produced = 0;

    public function produce(iterable $messages): void
    {
        foreach ($messages as $_) {
            $this->produced++;
        }
    }
}
