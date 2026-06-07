<?php

namespace KafkaBus\Commiter\Tests;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Commiter\Repositories\ArrayRepositorySource;
use KafkaBus\Commiter\Repositories\IdempotencyMessageRepository;
use Testo\Assert;
use Testo\Test;

#[Test]
final class IdempotencyMessageRepositoryTest
{
    public function usesHeaderKeyAndDelegatesToSource(): void
    {
        $source = new ArrayRepositorySource();
        $repository = new IdempotencyMessageRepository($source);

        $message = new ConsumerMessage(
            MessageFactory::for()
                ->withTopicKey('products')
                ->withHeaders([IdempotencyMessageRepository::HEADER_NAME => 'order-123'])
                ->make('payload')
        );

        Assert::same($repository->attempt($message)->key, 'order-123-products');
        Assert::same($repository->attempt($message)->number, 1);

        $repository->failed($message);
        Assert::same($source->get('order-123-products')?->number, 1);

        $repository->commit($message);
        Assert::notNull($source->get('order-123-products')?->commitedAt);
    }

    public function fallsBackToMessageIdWhenHeaderMissing(): void
    {
        $source = new ArrayRepositorySource();
        $repository = new IdempotencyMessageRepository($source);

        $message = new ConsumerMessage(
            MessageFactory::for()
                ->withTopicKey('products')
                ->withHeaders(['foo' => 'bar'])
                ->make('payload')
        );

        Assert::same($repository->attempt($message)->key, $message->msgId());
    }
}
