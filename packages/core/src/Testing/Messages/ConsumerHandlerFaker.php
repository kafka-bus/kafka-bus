<?php

namespace KafkaBus\Core\Testing\Messages;

final class ConsumerHandlerFaker
{
    public function __invoke(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
