<?php

namespace KafkaBus\Core\Consumers\Commiters;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use RdKafka\KafkaConsumer;

class DefaultCommiter implements CommiterInterface
{
    public function __construct(
        protected KafkaConsumer $consumer
    ) {
    }

    public function commit(ConsumerMessageInterface $consumerMessage): void
    {
        $this->consumer
            ->commitAsync($consumerMessage->original());
    }
}
