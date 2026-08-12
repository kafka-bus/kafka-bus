<?php

namespace KafkaBus\Messages\Tests\Data;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use KafkaBus\Messages\Data\Payload;
use KafkaBus\Workbench\Data\EventPayload;
use KafkaBus\Workbench\Data\ScheduledPayload;
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

    #[Test]
    public function date_attribute_is_casted_to_date_time_interface(): void
    {
        $payload = new EventPayload([
            'name' => 'order.created',
            'occurredAt' => '2020-01-01T06:00:00.000000+03:00',
        ]);

        Assert::instanceOf($payload->occurredAt, DateTimeInterface::class);
        Assert::equals($payload->occurredAt->getTimezone()->getName(), '+03:00');
    }

    #[Test]
    public function nullable_date_attribute_stays_null(): void
    {
        $payload = new EventPayload([
            'name' => 'order.created',
            'occurredAt' => null,
        ]);

        Assert::null($payload->occurredAt);
        Assert::null($payload->jsonSerialize()['occurredAt']);
    }

    #[Test]
    public function date_attribute_is_rolled_back_to_formatted_string_on_json_serialize(): void
    {
        $payload = new EventPayload([
            'name' => 'order.created',
            'occurredAt' => '2020-01-01T06:00:00.000000+03:00',
        ]);

        Assert::equals($payload->jsonSerialize()['occurredAt'], '2020-01-01T03:00:00.000000+00:00');
    }

    #[Test]
    public function custom_date_format_is_used_for_rollback(): void
    {
        $payload = new ScheduledPayload([
            'scheduledAt' => new DateTimeImmutable('2024-03-15 12:00:00', new DateTimeZone('+03:00')),
        ]);

        Assert::equals($payload->jsonSerialize()['scheduledAt'], '2024-03-15');
    }
}
