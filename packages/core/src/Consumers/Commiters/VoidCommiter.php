<?php

namespace KafkaBus\Core\Consumers\Commiters;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

class VoidCommiter implements CommiterInterface
{
    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
    }
}
