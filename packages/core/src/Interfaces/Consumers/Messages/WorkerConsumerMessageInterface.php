<?php

namespace KafkaBus\Core\Interfaces\Consumers\Messages;

interface WorkerConsumerMessageInterface extends ConsumerMessageInterface
{
    public function workerName(): string;
}
