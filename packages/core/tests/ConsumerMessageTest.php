<?php

namespace KafkaBus\Core\Tests;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Assertions\TestoAssertionDriver;
use KafkaBus\Core\Testing\BusFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Test;

#[Test]
final class ConsumerMessageTest
{
    public function canConsumeMessage(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $receiver = ReceiverBuilder::make($topicRegistry)
            ->add(new RouteInfo('products', new VoidConsumerHandlerFaker()))
            ->build();

        $busFaker = BusFaker::make($topicRegistry, new TestoAssertionDriver(), receiver: $receiver);

        $message = MessageFactory::for()
            ->withHeaders(['foo' => 'bar'])
            ->withTopicKey('products', $topicRegistry)
            ->make('test-message');

        $busFaker->dispatch(new ConsumerMessage($message));

        $busFaker->assertDispatchedTimes('products', 1);

        $busFaker->assertDispatched('products', function (ConsumerMessageInterface $message) {
            return $message->payload() == 'test-message'
                && $message->headers() == ['foo' => 'bar'];
        });
    }
}
