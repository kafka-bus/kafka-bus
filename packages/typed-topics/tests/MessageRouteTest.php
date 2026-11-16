<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics\Tests;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\ProvidesMessageFactory;
use KafkaBus\TypedTopics\HasMessageType;
use KafkaBus\TypedTopics\MessageRoutes;
use LogicException;
use RdKafka\Message;
use Testo\Assert;
use Testo\Test;

#[Test]
final class MessageRouteTest
{
    public function usesFactoryProvidedByTypedRouteHandler(): void
    {
        $factory = new class () implements MessageFactoryInterface {
            public int $calls = 0;

            public function fromKafka(ConsumerMessageInterface $message): mixed
            {
                ++$this->calls;
                return ['decoded' => $message->payload()];
            }
        };

        $handler = new class ($factory) implements ProvidesMessageFactory {
            /** @var list<array<string, string>> */
            public array $received = [];

            public function __construct(private MessageFactoryInterface $factory)
            {
            }

            public function messageFactory(): MessageFactoryInterface
            {
                return $this->factory;
            }

            /** @param array<string, string> $message */
            public function __invoke(array $message): void
            {
                $this->received[] = $message;
            }
        };

        $record = new class () implements ConsumerMessageInterface {
            public function msgId(): string
            {
                return 'record-1';
            }
            public function topicName(): string
            {
                return 'events';
            }
            public function key(): ?string
            {
                return null;
            }
            public function payload(): string
            {
                return 'raw-value';
            }
            public function headers(): array
            {
                return [HasMessageType::HEADER_NAME => 'custom'];
            }
            public function original(): Message
            {
                throw new LogicException('Native record is not needed');
            }
        };

        $routes = (new MessageRoutes())->on('custom', $handler);

        $decoded = $routes->messageFactory()->fromKafka($record);

        $routes->dispatch($decoded, 'custom');

        Assert::same($factory->calls, 1);
        Assert::same($handler->received, [['decoded' => 'raw-value']]);
    }
}
