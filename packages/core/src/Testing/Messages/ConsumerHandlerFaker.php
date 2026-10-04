<?php

namespace KafkaBus\Core\Testing\Messages;

final readonly class ConsumerHandlerFaker
{
    public function __invoke(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
