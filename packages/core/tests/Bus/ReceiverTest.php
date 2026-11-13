<?php

namespace KafkaBus\Core\Tests\Bus;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Exceptions\Consumers\RouteConsumerException;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use RdKafka\Message;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class ReceiverTest
{
    public function dispatchesToRegisteredHandler(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'));

        $received = null;

        $receiver = ReceiverBuilder::make($topicRegistry)
            ->add(new RouteInfo('orders', function (string $message) use (&$received): void {
                $received = $message;
            }))
            ->build();

        $message = MessageFactory::for()
            ->withTopicKey('orders', $topicRegistry)
            ->make('order-payload');

        $receiver->dispatch(new ConsumerMessage($message));

        Assert::equals($received, 'order-payload');
    }

    public function throwsMessageConsumerNotHandledExceptionForUnmappedTopic(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'))
            ->add(new Topic('events.products.1', 'products'));

        $receiver = ReceiverBuilder::make($topicRegistry)
            ->add(new RouteInfo('orders', new VoidConsumerHandlerFaker()))
            ->build();

        $message = MessageFactory::for()
            ->withTopicKey('products')
            ->make('product-payload');


        Expect::exception(MessageConsumerNotHandledException::class);

        try {
            $receiver->dispatch(new ConsumerMessage($message));
        }
        catch (MessageConsumerNotHandledException $exception) {
            Assert::true($exception->getPrevious() instanceof RouteConsumerException);

            throw $exception;
        }
    }

    public function injectsRecordMetadataAndKeepsOptionalDefaults(): void
    {
        $registry = (new TopicRegistry())->add(new Topic('events.orders.1', 'orders'));
        $received = [];
        $handler = static function (
            array $message,
            int $partition,
            ConsumerMessageInterface $source,
            array $headers,
            int $offset,
            string $payload,
            string $label = 'default',
            ?string $key = 'default-key',
            string $topic = '',
            ?Message $raw = null,
        ) use (&$received): void {
            $received = [$message, $partition, $source, $headers, $offset, $payload, $label, $key, $topic, $raw];
        };
        $receiver = ReceiverBuilder::make($registry)->add(new RouteInfo('orders', $handler))->build();
        $headers = ['source' => 'replay'];
        $payload = '{"id":42}';
        $raw = MessageFactory::for()
            ->withTopicKey('orders', $registry)
            ->withHeaders($headers)
            ->withPartition(2)
            ->withOffset(17)
            ->make($payload);
        $record = new ConsumerMessage($raw);
        $receiver->dispatch($record);

        Assert::same($received, [['id' => 42], 2, $record, $headers, 17, $payload, 'default', null, 'events.orders.1', $raw]);
    }
}
