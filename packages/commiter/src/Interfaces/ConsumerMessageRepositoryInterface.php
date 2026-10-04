<?php

namespace KafkaBus\Commiter\Interfaces;

use KafkaBus\Commiter\Attempt;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;

interface ConsumerMessageRepositoryInterface
{
    public function attempt(ConsumerMessageInterface $message): Attempt;

    public function failed(ConsumerMessageInterface $message): void;

    public function commit(ConsumerMessageInterface $message): void;
}
