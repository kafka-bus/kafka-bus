<?php

namespace KafkaBus\Core\Tests\Bus;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Receivers\LazyReceiver;
use KafkaBus\Core\Receivers\ReceiverInterface;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Assert;
use Testo\Test;

#[Test]
final class LazyReceiverTest
{
    public function buildsReceiverOnlyOnFirstDispatch(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $builds = 0;

        $lazyReceiver = new LazyReceiver(function () use (&$builds, $topicRegistry): ReceiverInterface {
            $builds++;

            return ReceiverBuilder::make($topicRegistry)
                ->add(new RouteInfo('products', new VoidConsumerHandlerFaker()))
                ->build();
        });

        Assert::same($builds, 0);

        $message = MessageFactory::for()
            ->withTopicKey('products', $topicRegistry)
            ->make('test-message');

        $lazyReceiver->dispatch(new ConsumerMessage($message));

        Assert::same($builds, 1);

        $lazyReceiver->dispatch(new ConsumerMessage($message));

        Assert::same($builds, 1);
    }
}
