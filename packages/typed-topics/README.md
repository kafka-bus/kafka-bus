# Kafka Bus Typed Topics

Send several message types through one Kafka topic. The `x-message-type` header identifies the payload format. On the consumer, Kafka Bus decodes the message before it enters the receiver pipeline.

```bash
composer require kafka-bus/typed-topics
```

Requires Kafka Bus Core 2.1.3 or newer in the 2.x series.

The examples use `JsonMessage` and `JsonMessageFactory` from `kafka-bus/messages`. Install that package when using these helpers. The factory result is passed as the handler's first argument.

## Publish messages

Implement `HasMessageType` on each message class. **`PublisherTypedTopicMiddleware` is required on every publisher route for typed messages.** The middleware reads `getMessageType()` and writes its value to the `x-message-type` header used by the receiver to select a route.

```php
use KafkaBus\Messages\JsonMessage;
use KafkaBus\TypedTopics\HasMessageType;

final class DeliveryUpdated extends JsonMessage implements HasMessageType
{
    public const TYPE = 'delivery.updated';

    public function getMessageType(): string
    {
        return self::TYPE;
    }
}

final class DeliveryCompleted extends JsonMessage implements HasMessageType
{
    public const TYPE = 'delivery.completed';

    public function getMessageType(): string
    {
        return self::TYPE;
    }
}
```

Register each producer message class against the same topic key and publish through the bus as usual. Use `HasKey` on messages that need a Kafka partition key.

```php
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\TypedTopics\PublisherTypedTopicMiddleware;

$options = new Options(middleware: [new PublisherTypedTopicMiddleware()]);

$publisherRoutes
    ->add(DeliveryUpdated::class, 'delivery-events', $options)
    ->add(DeliveryCompleted::class, 'delivery-events', $options);

$bus->publish(DeliveryUpdated::from(['delivery_id' => 42]));
```

## Receive messages

Register a `TypedTopicHandler` instance as the topic handler. Pair each header value with a callable. Supply a factory as the third argument to `on()` or use the `#[MessageFactory]` attribute. An annotated closure also works as a handler.

```php
use KafkaBus\Core\Receivers\Attributes\MessageFactory;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Messages\Factories\JsonMessageFactory;
use KafkaBus\TypedTopics\MessageRoutes;
use KafkaBus\TypedTopics\TypedTopicHandler;

final class DeliveryTopicHandler extends TypedTopicHandler
{
    protected function routes(MessageRoutes $routes): void
    {
        $routes
            ->on(DeliveryUpdated::TYPE, $this->onUpdated(...))
            ->on(DeliveryCompleted::TYPE, $this->onCompleted(...));
    }

    #[MessageFactory(new JsonMessageFactory(DeliveryUpdated::class))]
    private function onUpdated( DeliveryUpdated $message): void 
    {
        // Apply the delivery update.
    }

    #[MessageFactory(new JsonMessageFactory(DeliveryCompleted::class))]
    private function onCompleted(DeliveryCompleted $message): void
    {
        // Apply the delivery result.
    }
}

$receiverBuilder->add(new RouteInfo('delivery-events', new DeliveryTopicHandler()));
```

The first parameter receives the decoded message. Add any of `$headers`, `$offset`, `$partition`, `$key`, `$topic` or `$topicName` after it in any order. Use `ConsumerMessageInterface` or `RdKafka\Message` as the parameter type to receive the full record. Optional parameters outside these conventions keep their defaults. The same arguments are available to ordinary receiver handlers and to the callbacks registered with `whenHeaderMissing()` or `onUnroutable()`.

### Records without a matching route

Use `whenHeaderMissing($this->onUpdated(...))` in `routes()` if records without an `x-message-type` header have a known legacy format. Records with an empty or unknown type still follow the unroutable policy.

By default, an unroutable record passes through the receiver pipeline and the handler returns successfully. Offset commits follow the application's existing Kafka Bus configuration. To change the policy, configure one of these methods in `routes()`.

- `onUnroutableLog($logger)` logs an error with the reason, type, topic, partition and offset, then completes processing.
- `onUnroutableThrow()` raises an exception through the normal receiver error path.
- `onUnroutable($handler)` passes an `UnroutableMessage` with the original record and reason to a custom handler.

Factory and business-handler errors follow the normal receiver error path.
