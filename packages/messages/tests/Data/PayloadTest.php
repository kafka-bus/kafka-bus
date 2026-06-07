<?php

namespace KafkaBus\Messages\Tests\Data;

use KafkaBus\Messages\Data\Payload;
use Testo\Assert;
use Testo\Test;

class PayloadTest
{
    #[Test]
    public function get_attribute(): void
    {
        $payload = new Payload(['test' => 'foo-bar']);

        Assert::equals($payload->test, 'foo-bar'); // @phpstan-ignore-line
    }

    #[Test]
    public function get_attribute_as_array(): void
    {
        $payload = new Payload(['test' => 'foo-bar']);

        Assert::equals($payload['test'], 'foo-bar');
    }

    #[Test]
    public function get_all_attributes(): void
    {
        $payload = new Payload(['test' => 'foo-bar']);

        Assert::equals($payload->jsonSerialize(), ['test' => 'foo-bar']);
    }
}
