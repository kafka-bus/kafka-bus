<?php

namespace KafkaBus\Commiter\Tests;

use KafkaBus\Commiter\Interfaces\HasIdempotency;
use KafkaBus\Commiter\Middleware\PublisherIdempotencyMiddleware;
use KafkaBus\Commiter\Repositories\IdempotencyMessageRepository;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\PublisherStreamFactory;
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Testing\Assertions\TestoAssertionDriver;
use KafkaBus\Core\Testing\BusFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Test;

#[Test]
final class ProducerIdempotencyMiddlewareTest
{
    public function setsHeaderForHasIdempotencyMessage(): void
    {
        $message = new class () implements ProducerMessageInterface, HasIdempotency {
            public function toPayload(): string
            {
                return 'payload';
            }

            public function getIdempotencyKey(): string
            {
                return 'idem-1';
            }
        };

        $busFaker = $this->buildBusFaker($message::class);

        $busFaker->publish($message);

        $busFaker->assertPublished(
            $message::class,
            static fn (ProducerMessage $producerMessage): bool =>
                ($producerMessage->headers[IdempotencyMessageRepository::HEADER_NAME] ?? null) === 'idem-1',
        );
    }

    public function doesNotSetHeaderForRegularMessage(): void
    {
        $message = new class () implements ProducerMessageInterface {
            public function toPayload(): string
            {
                return 'payload';
            }
        };

        $busFaker = $this->buildBusFaker($message::class);

        $busFaker->publish($message);

        $busFaker->assertPublished(
            $message::class,
            static fn (ProducerMessage $producerMessage): bool =>
                ! \array_key_exists(IdempotencyMessageRepository::HEADER_NAME, $producerMessage->headers),
        );
    }

    /**
     * @param class-string<ProducerMessageInterface> $messageClass
     * @return BusFaker
     */
    private function buildBusFaker(string $messageClass): BusFaker
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $routes = PublisherRoutesBuilder::make($topicRegistry)
            ->add($messageClass, 'products', new Options(middleware: [new PublisherIdempotencyMiddleware()]))
            ->build();

        return BusFaker::make(
            $topicRegistry,
            new TestoAssertionDriver(),
            new PublisherFactory(new PublisherStreamFactory(), $routes),
        );
    }
}
