<?php

namespace KafkaBus\Core\Consumers\Commiters;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;

interface CommiterInterface
{
    public function commit(ConsumerMessageInterface $consumerMessage): void;
}
