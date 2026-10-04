<?php

namespace KafkaBus\Core\Tests;

use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\PublisherStreamFactory;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Testing\Assertions\TestoAssertionDriver;
use KafkaBus\Core\Testing\BusFaker;
use KafkaBus\Core\Testing\Messages\ProducerMessageFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Test;

#[Test]
final class ProduceMessageTest
{
    public function canProduceMessage(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $routes = PublisherRoutesBuilder::make($topicRegistry)
            ->add(ProducerMessageFaker::class, 'products')
            ->build();

        $busFaker = BusFaker::make(
            $topicRegistry,
            new TestoAssertionDriver(),
            new PublisherFactory(new PublisherStreamFactory(), $routes),
        );

        $busFaker->publish(new ProducerMessageFaker('test-message', ['foo' => 'bar'], 5));

        $busFaker->assertPublishedTimes(ProducerMessageFaker::class, 1);

        $busFaker->assertPublished(ProducerMessageFaker::class, function (ProducerMessage $message) {
            return $message->payload == 'test-message'
                && $message->partition == 5
                && $message->headers == ['foo' => 'bar'];
        });
    }
}
