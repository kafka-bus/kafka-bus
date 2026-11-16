<?php

declare(strict_types=1);

namespace KafkaBus\Core\Tests\Routing;

use KafkaBus\Core\Receivers\Attributes\MessageFactory;
use KafkaBus\Core\Receivers\Messages\JsonMessageFactory;
use KafkaBus\Core\Receivers\Messages\MessageFactoryInterface;
use KafkaBus\Core\Receivers\Messages\ProvidesMessageFactory;
use KafkaBus\Core\Receivers\Messages\StringMessageFactory;
use KafkaBus\Core\Receivers\Routing\MessageFactoryExtractor;
use Testo\Assert;
use Testo\Test;

#[Test]
final class MessageFactoryExtractorTest
{
    public function infersFactoryForEveryCallableForm(): void
    {
        $extractor = new MessageFactoryExtractor();
        $object = new ExtractorHandler();

        foreach ([
            [ExtractorHandler::class, 'handle'],
            ExtractorHandler::class . '::handle',
            ExtractorHandler::handle(...),
            [$object, 'handleInstance'],
            $object,
            __NAMESPACE__ . '\\extractorArrayHandler',
            static function (array $message): void {
            },
        ] as $handler) {
            Assert::true($extractor->extract($handler) instanceof JsonMessageFactory);
        }
    }

    public function preservesFactoryAttributesOnMethodsAndFunctions(): void
    {
        $extractor = new MessageFactoryExtractor();

        foreach ([
            [ExtractorHandler::class, 'attributed'],
            ExtractorHandler::class . '::attributed',
            __NAMESPACE__ . '\\extractorAttributedHandler',
        ] as $handler) {
            Assert::true($extractor->extract($handler) instanceof StringMessageFactory);
        }
    }

    public function keepsProvidedFactoryAheadOfAttributeAndArgumentInference(): void
    {
        $handler = new class () implements ProvidesMessageFactory {
            #[MessageFactory(new StringMessageFactory())]
            public function __invoke(string $message): void
            {
            }

            public function messageFactory(): MessageFactoryInterface
            {
                return new JsonMessageFactory();
            }
        };

        Assert::true((new MessageFactoryExtractor())->extract($handler) instanceof JsonMessageFactory);
    }
}

final class ExtractorHandler
{
    public static function handle(array $message): void
    {
    }

    public function handleInstance(array $message): void
    {
    }

    public function __invoke(array $message): void
    {
    }

    #[MessageFactory(new StringMessageFactory())]
    public static function attributed(mixed $message): void
    {
    }
}

function extractorArrayHandler(array $message): void
{
}

#[MessageFactory(new StringMessageFactory())]
function extractorAttributedHandler(mixed $message): void
{
}
