<?php

declare(strict_types=1);

namespace KafkaBus\Core\Tests\Routing;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Routing\HandlerMetadata;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use RdKafka\Message;
use Testo\Assert;
use Testo\Test;

#[Test]
final class HandlerMetadataTest
{
    public function resolvesMetadataByParameterNameAndRecordType(): void
    {
        $handler = static function (
            mixed $message,
            int $partition,
            ConsumerMessageInterface $source,
            string $topicName,
            int $offset,
            array $headers,
            Message $raw,
            string $payload,
            ?string $key,
            string $topic,
        ): void {
        };
        $headers = ['source' => 'orders'];
        $raw = MessageFactory::for()
            ->withTopicKey('orders.events')
            ->withHeaders($headers)
            ->withPartition(2)
            ->withOffset(17)
            ->withKey('order-42')
            ->make('raw-payload');
        $record = new ConsumerMessage($raw);

        Assert::same(HandlerMetadata::fromHandler($handler)->arguments($record), [
            'partition' => 2,
            'source' => $record,
            'topicName' => 'orders.events',
            'offset' => 17,
            'headers' => $headers,
            'raw' => $raw,
            'payload' => 'raw-payload',
            'key' => 'order-42',
            'topic' => 'orders.events',
        ]);
    }

    public function skipsTheFirstParameterUnknownParametersAndVariadics(): void
    {
        $handler = static function (string $payload, string $label = 'default', int ...$offset): void {
        };
        $record = new ConsumerMessage(MessageFactory::for()->make('raw-payload'));

        Assert::same(HandlerMetadata::fromHandler($handler)->arguments($record), []);
    }

    public function keepsEmptyHeadersAndNullKey(): void
    {
        $handler = static function (mixed $message, array $headers, ?string $key): void {
        };
        $record = new ConsumerMessage(MessageFactory::for()->make('raw-payload'));

        Assert::same(HandlerMetadata::fromHandler($handler)->arguments($record), [
            'headers' => [],
            'key' => null,
        ]);
    }
}
