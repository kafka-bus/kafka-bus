<?php

namespace KafkaBus\Messages\Tests;

use KafkaBus\Messages\JsonMessage;
use Testo\Assert;
use Testo\Test;

class JsonMessageTest
{
    #[Test]
    public function convert_payload_for_kafka(): void
    {
        $message = new JsonMessage(['foo' => 'bar']);

        Assert::equals($message->toPayload(), '{"foo":"bar"}');
    }
}
