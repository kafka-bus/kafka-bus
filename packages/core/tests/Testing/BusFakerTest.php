<?php

namespace KafkaBus\Core\Tests\Testing;

use KafkaBus\Core\Consumers\ConsumerConfig;
use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\PublisherStreamFactory;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Assertions\PHPUnitAssertionDriver;
use KafkaBus\Core\Testing\Assertions\TestoAssertionDriver;
use KafkaBus\Core\Testing\BusFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\ProducerMessageFaker;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use PHPUnit\Framework\ExpectationFailedException;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Expect;
use Testo\Test;

#[Test]
final class BusFakerTest
{
    public function assertsPublishedMessages(): void
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

        $busFaker->assertNothingPublished();
        $busFaker->assertNotPublished(ProducerMessageFaker::class);

        $busFaker->publish(new ProducerMessageFaker('test-message', ['foo' => 'bar'], 5));

        $busFaker->assertPublished(ProducerMessageFaker::class);
        $busFaker->assertPublished(
            ProducerMessageFaker::class,
            static fn ($message) => $message->payload === 'test-message',
        );
        $busFaker->assertPublishedTimes(ProducerMessageFaker::class, 1);
    }

    public function assertsCommittedMessagesViaRealConsumeAndCommit(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'));

        $busFaker = BusFaker::make($topicRegistry, new TestoAssertionDriver());

        $busFaker->assertNothingCommitted();

        $busFaker->addMessage(
            MessageFactory::for()->withTopicKey('orders')->make('order-payload'),
        );

        $consumer = $busFaker->connection()->createConsumer(new ConsumerConfig());
        $message = $consumer->getMessage();
        $consumer->commit($message);

        $busFaker->assertCommitted('orders');
        $busFaker->assertCommitted(
            'orders',
            static fn (ConsumerMessageInterface $message): bool => $message->payload() === 'order-payload',
        );
        $busFaker->assertCommittedTimes('orders', 1);

        // Committing never goes through Bus::dispatch(), so dispatch-side stays empty.
        $busFaker->assertNothingDispatched();
    }

    public function assertsDispatchedMessagesIndependentlyOfCommits(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $receiver = ReceiverBuilder::make($topicRegistry)
            ->add(new RouteInfo('products', new VoidConsumerHandlerFaker()))
            ->build();

        $busFaker = BusFaker::make(
            $topicRegistry,
            new TestoAssertionDriver(),
            receiver: $receiver,
        );

        $busFaker->assertNothingDispatched();

        $message = MessageFactory::for()
            ->withTopicKey('products', $topicRegistry)
            ->make('payload');

        $busFaker->dispatch(new ConsumerMessage($message));

        $busFaker->assertDispatched('products');
        $busFaker->assertDispatched(
            'products',
            static fn (ConsumerMessageInterface $message): bool => $message->payload() === 'payload',
        );
        $busFaker->assertDispatchedTimes('products', 1);

        // Dispatch alone never commits a real Kafka offset.
        $busFaker->assertNothingCommitted();
    }

    public function testoDriverThrowsTestoExceptionOnFailure(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $busFaker = BusFaker::make($topicRegistry, new TestoAssertionDriver());

        Expect::exception(AssertionException::class);

        $busFaker->assertPublished(ProducerMessageFaker::class);
    }

    public function phpUnitDriverThrowsPHPUnitExceptionOnFailure(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $busFaker = BusFaker::make($topicRegistry, new PHPUnitAssertionDriver());

        Expect::exception(ExpectationFailedException::class);

        $busFaker->assertPublished(ProducerMessageFaker::class);
    }
}
