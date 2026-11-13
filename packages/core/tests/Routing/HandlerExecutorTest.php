<?php

declare(strict_types=1);

namespace KafkaBus\Core\Tests\Routing;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Attributes\MessageFactory;
use KafkaBus\Core\Receivers\Messages\JsonMessageFactory;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\ProvidesMessageFactory;
use KafkaBus\Core\Receivers\Messages\StringMessageFactory;
use KafkaBus\Core\Receivers\Routing\HandlerExecutor;
use KafkaBus\Core\Testing\Consumers\MessageFactory as KafkaMessageFactory;
use Testo\Assert;
use Testo\Test;

#[Test]
final class HandlerExecutorTest
{
    public function invokesHandlerWithDecodedMessageAndNamedMetadata(): void
    {
        $received = [];
        $handler = static function (
            array $message,
            string $label = 'default',
            int $offset = -1,
            ?ConsumerMessageInterface $source = null,
        ) use (&$received): void {
            $received = [$message, $label, $offset, $source];
        };
        $record = $this->record();
        $executor = HandlerExecutor::fromHandler($handler);

        $decoded = $executor->fromKafka($record);
        Assert::same($decoded, ['id' => 42]);
        Assert::same($received, []);

        $executor->execute($decoded, $record);

        Assert::same($received, [['id' => 42], 'default', 17, $record]);
    }

    public function usesNativeRecordWhenNoFactoryIsDeclaredOrInferred(): void
    {
        $handler = static function (ConsumerMessageInterface $message): void {
        };
        $record = $this->record();

        Assert::same(HandlerExecutor::fromHandler($handler)->fromKafka($record), $record);
    }

    public function usesFactoryFromCallableAttribute(): void
    {
        $handler = #[MessageFactory(new StringMessageFactory())]
            static function (mixed $message): void {
            };

        Assert::same(HandlerExecutor::fromHandler($handler)->fromKafka($this->record()), '{"id":42}');
    }

    public function honorsFactoryProvidedByInvokableHandler(): void
    {
        $handler = new class () implements ProvidesMessageFactory {
            public function __invoke(mixed $message): void
            {
            }

            public function messageFactory(): MessageFactoryInterface
            {
                return new JsonMessageFactory();
            }
        };

        Assert::same(HandlerExecutor::fromHandler($handler)->fromKafka($this->record()), ['id' => 42]);
    }

    public function explicitFactoryOverridesFactoryFromAttribute(): void
    {
        $received = null;
        $handler = #[MessageFactory(new JsonMessageFactory())]
            static function (array|string $message) use (&$received): void {
                $received = $message;
            };
        $record = $this->record();
        $executor = HandlerExecutor::fromHandler($handler, new StringMessageFactory());

        $executor->execute($executor->fromKafka($record), $record);

        Assert::same($received, '{"id":42}');
    }

    private function record(): ConsumerMessage
    {
        return new ConsumerMessage(KafkaMessageFactory::for()->withOffset(17)->make('{"id":42}'));
    }
}
