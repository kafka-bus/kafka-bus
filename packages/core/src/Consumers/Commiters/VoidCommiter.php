<?php

namespace KafkaBus\Core\Consumers\Commiters;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;

class VoidCommiter implements CommiterInterface
{
    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
    }
}
