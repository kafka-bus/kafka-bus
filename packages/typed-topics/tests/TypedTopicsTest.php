<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics\Tests;

use Closure;
use InvalidArgumentException;
use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Producers\Messages\HasKey;
use KafkaBus\Core\Producers\Messages\ProducerMessage;
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\Core\Receivers\Attributes\MessageFactory;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Testing\Assertions\TestoAssertionDriver;
use KafkaBus\Core\Testing\BusFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory as KafkaMessageFactory;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Messages\Factories\JsonMessageFactory;
use KafkaBus\Messages\JsonMessage;
use KafkaBus\TypedTopics\HasMessageType;
use KafkaBus\TypedTopics\MessageRoutes;
use KafkaBus\TypedTopics\PublisherTypedTopicMiddleware;
use KafkaBus\TypedTopics\TypedTopicHandler;
use KafkaBus\TypedTopics\UnroutableMessageException;
use KafkaBus\TypedTopics\UnroutableReason;
use Psr\Log\AbstractLogger;
use Stringable;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class TypedTopicsTest
{
    public function publishesBothTypes(): void
    {
        $registry = $this->topicRegistry();

        $publisherRoutes = PublisherRoutesBuilder::make($registry)
            ->add(Updated::class, 'delivery', new Options(middleware: [new PublisherTypedTopicMiddleware()]))
            ->add(Completed::class, 'delivery', new Options(middleware: [new PublisherTypedTopicMiddleware()]))
            ->build();

        $bus = BusFaker::make($registry, new TestoAssertionDriver(), new PublisherFactory(routes: $publisherRoutes));

        $bus->publish(Updated::from(['id' => 42]));
        $bus->publish(Completed::from(['id' => 31]));

        $bus->assertPublished(Updated::class, static function (ProducerMessage $message): bool {
            return $message->headers[HasMessageType::HEADER_NAME] === 'delivery.updated';
        });

        $bus->assertPublished(Updated::class, static function (ProducerMessage $message): bool {
            return $message->headers[HasMessageType::HEADER_NAME] === 'delivery.completed';
        });

        Assert::equals(array_column($bus->allPublished(), 'key'), ['42', '31']);
        Assert::equals(array_column($bus->allPublished(), 'headers'), [
            [HasMessageType::HEADER_NAME => 'delivery.updated'],
            [HasMessageType::HEADER_NAME => 'delivery.completed'],
        ]);
    }

    public function receiverBothTypes(): void
    {
        $handler = new TestTopicHandler();
        $registry = $this->topicRegistry();

        $receiver = ReceiverBuilder::make($registry)
            ->add(new RouteInfo('delivery', $handler))
            ->build();

        $bus = BusFaker::make($registry, new TestoAssertionDriver(), receiver: $receiver);

        $bus->dispatch($this->message([HasMessageType::HEADER_NAME => 'delivery.updated'], ['id' => 52]));
        $bus->dispatch($this->message([HasMessageType::HEADER_NAME => 'delivery.completed'], ['id' => 32]));

        $dispatched = array_map(fn (ConsumerMessageInterface $message) => $message->original(), $bus->allDispatched());

        Assert::equals(array_column($dispatched, 'headers'), [
            [HasMessageType::HEADER_NAME => 'delivery.updated'],
            [HasMessageType::HEADER_NAME => 'delivery.completed'],
        ]);

        Assert::array($handler->received)->hasCount(2);
        Assert::object($handler->received[0])->instanceOf(Updated::class);
        Assert::object($handler->received[1])->instanceOf(Completed::class);
    }

    public function usesFallbackOnlyWhenHeaderIsMissing(): void
    {
        $handler = new TestTopicHandler(static function (MessageRoutes $routes): void {
            $routes->onUnroutableThrow();
        }, useLegacyRoute: true);

        $receiver = ReceiverBuilder::make($this->topicRegistry())
            ->add(new RouteInfo('delivery', $handler))->build();

        $legacy = $this->message();
        unset($legacy->original()->headers);

        $receiver->dispatch($legacy);

        Assert::true($handler->received[0] instanceof Updated);

        Expect::exception(MessageConsumerNotHandledException::class);

        try {
            $receiver->dispatch($this->message([HasMessageType::HEADER_NAME => 'future.type']));
        }
        catch (MessageConsumerNotHandledException $error) {
            $previous = $error->getPrevious();

            Assert::true($previous instanceof UnroutableMessageException);
            Assert::equals($previous->unroutable->reason, UnroutableReason::UnknownType);

            throw $error;
        }
    }

    public function logsUnroutableMetadata(): void
    {
        $logger = new class () extends AbstractLogger {
            /** @var list<array{mixed, array<mixed>}> */
            public array $records = [];

            /** @param string|Stringable $message */
            public function log($level, $message, array $context = []): void
            {
                $this->records[] = [$level, $context];
            }
        };

        $handler = new TestTopicHandler(static function (MessageRoutes $routes) use ($logger): void {
            $routes->onUnroutableLog($logger);
        });

        $receiver = ReceiverBuilder::make($this->topicRegistry())->add(new RouteInfo('delivery', $handler))->build();
        $receiver->dispatch($this->message());

        Assert::equals($logger->records, [['error', [
            'reason' => 'missing_header',
            'type' => null,
            'topic' => 'delivery.events',
            'partition' => 2,
            'offset' => 17,
        ]]]);
    }

    public function rejectsDuplicateHeaderValues(): void
    {
        $handler = static function (Updated $message): void {
        };

        $routes = (new MessageRoutes())->on('delivery.updated', $handler);

        Expect::exception(InvalidArgumentException::class);

        $routes->on('delivery.updated', $handler);
    }

    private function topicRegistry(): TopicRegistry
    {
        return (new TopicRegistry())
            ->add(new Topic('delivery.events', 'delivery'));
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $payload
     */
    private function message(array $headers = [], array $payload = ['id' => 42]): ConsumerMessage
    {
        return new ConsumerMessage(KafkaMessageFactory::for()
            ->withTopicKey('delivery.events')
            ->withHeaders($headers)
            ->withPartition(2)
            ->withOffset(17)
            ->make(json_encode($payload)));
    }
}

final class TestTopicHandler extends TypedTopicHandler
{
    /**
     * @var list<Updated|Completed>
     */
    public array $received = [];

    /**
     * @param (Closure(MessageRoutes): void)|null $configure
     * @param bool $useLegacyRoute
     */
    public function __construct(
        private ?Closure $configure = null,
        private bool $useLegacyRoute = false
    ) {
    }

    protected function routes(MessageRoutes $routes): void
    {
        $routes->on('delivery.updated', $this->onUpdated(...));

        $routes->on('delivery.completed', #[MessageFactory(new JsonMessageFactory(Completed::class))]
            function (Completed $message): void {
                $this->received[] = $message;
            });

        if ($this->useLegacyRoute) {
            $routes->whenHeaderMissing($this->onUpdated(...));
        }

        if ($this->configure !== null) {
            ($this->configure)($routes);
        }
    }

    #[MessageFactory(new JsonMessageFactory(Updated::class))]
    private function onUpdated(Updated $message): void
    {
        $this->received[] = $message;
    }
}

/**
 * @property int $id
 */
final class Updated extends JsonMessage implements HasMessageType, HasKey
{
    public function getMessageType(): string
    {
        return 'delivery.updated';
    }

    public function getKey(): string
    {
        return (string) $this->id;
    }
}

/**
 * @property int $id
 */
final class Completed extends JsonMessage implements HasMessageType, HasKey
{
    public function getMessageType(): string
    {
        return 'delivery.completed';
    }

    public function getKey(): string
    {
        return (string) $this->id;
    }
}
