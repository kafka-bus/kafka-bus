<?php

namespace KafkaBus\Commiter\Interfaces;

use KafkaBus\Core\Interfaces\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Commiter\Attempt;

interface ConsumerMessageRepositoryInterface
{
    public function attempt(ConsumerMessageInterface $message): Attempt;

    public function failed(ConsumerMessageInterface $message): void;

    public function commit(ConsumerMessageInterface $message): void;
}
