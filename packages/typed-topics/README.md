# Kafka Bus Typed Topics

Route multiple message types through one Apache Kafka topic in PHP. The `x-message-type` header selects the message factory and handler while the outer bus route selects the topic.

## Installation

Requires PHP 8.2+ and Kafka Bus Core, whose Kafka adapter requires `ext-rdkafka`.

```bash
composer require kafka-bus/typed-topics
```

The examples below also use `kafka-bus/messages`:

```bash
composer require kafka-bus/messages
```

For connections and worker setup, follow the [complete quick start](https://github.com/kafka-bus/kafka-bus#quick-start). The following snippets replace its message and route definitions before constructing the bus.

## 1. Define message types

```php
use KafkaBus\Messages\JsonMessage;
use KafkaBus\TypedTopics\HasMessageType;

final class OrderPlaced extends JsonMessage implements HasMessageType
{
    public function getMessageType(): string
    {
        return 'order.placed';
    }
}

final class OrderCancelled extends JsonMessage implements HasMessageType
{
    public function getMessageType(): string
    {
        return 'order.cancelled';
    }
}
```

Use stable type names as part of your message contract.

## 2. Register publisher routes

Both classes share the `orders` topic key. Add the middleware to each route so outgoing records carry the type header:

```php
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\TypedTopics\PublisherTypedTopicMiddleware;

$options = new Options(middleware: [new PublisherTypedTopicMiddleware()]);
$routes = PublisherRoutesBuilder::make($topics)
    ->add(OrderPlaced::class, 'orders', $options)
    ->add(OrderCancelled::class, 'orders', $options)
    ->build();
```

Pass `$routes` to `PublisherFactory(routes: $routes)` when constructing the bus. Then publish with `$bus->publish(OrderPlaced::from(['order_id' => 42]))`.

## 3. Register a typed receiver

```php
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Messages\Factories\JsonMessageFactory;
use KafkaBus\TypedTopics\MessageRoutes;
use KafkaBus\TypedTopics\TypedTopicHandler;

final class OrdersHandler extends TypedTopicHandler
{
    protected function routes(MessageRoutes $routes): void
    {
        $routes
            ->on('order.placed', static function (OrderPlaced $message): void {
                echo 'Placed: ' . $message['order_id'] . PHP_EOL;
            }, new JsonMessageFactory(OrderPlaced::class))
            ->on('order.cancelled', static function (OrderCancelled $message): void {
                echo 'Cancelled: ' . $message['order_id'] . PHP_EOL;
            }, new JsonMessageFactory(OrderCancelled::class))
            ->onUnroutableThrow();
    }
}

$receiver = ReceiverBuilder::make($topics)
    ->add(new RouteInfo('orders', new OrdersHandler()))->build();
```

Pass `$receiver` to the bus constructor. A worker subscribed to `orders.events` now dispatches both types. You can also select factories through `#[MessageFactory(...)]` or an invokable handler implementing `ProvidesMessageFactory`.

## Unknown types and legacy messages

`whenHeaderMissing($handler, $factory)` supplies a legacy route for records without a type header. Unknown types use the unroutable policy:

- `onUnroutableThrow()` raises an exception through the receiver error path.
- `onUnroutableLog($logger)` logs the reason and record metadata, then returns normally.
- `onUnroutable($handler)` receives an `UnroutableMessage` containing the record, reason and message type.

With no unroutable handler, the record is skipped silently. A worker can commit its offset after dispatch returns normally. Choose the policy explicitly for your application. Factory and business-handler failures propagate through the receiver pipeline.

The outer `TypedTopicHandler::__invoke()` receives headers through Core metadata injection. Inner callbacks registered with `on()` receive the decoded message; capture any additional dependencies in the callable.

[Issues and contributions](https://github.com/kafka-bus/kafka-bus) · [Releases](https://github.com/kafka-bus/kafka-bus/releases) · [MIT License](https://github.com/kafka-bus/kafka-bus/blob/HEAD/LICENSE.md).
