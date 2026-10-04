<?php

namespace KafkaBus\Core\Consumers\Commiters;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

interface CommiterInterface
{
    public function commit(ConsumerMessageInterface $consumerMessage): void;
}
