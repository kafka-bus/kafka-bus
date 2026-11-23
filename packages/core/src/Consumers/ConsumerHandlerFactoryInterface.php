<?php

namespace KafkaBus\Core\Consumers;

interface ConsumerHandlerFactoryInterface
{
    public function create(): ConsumerHandlerInterface;
}
