<?php

namespace KafkaBus\Core\Tests\Bus;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Exceptions\Consumers\RouteConsumerException;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
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
}
