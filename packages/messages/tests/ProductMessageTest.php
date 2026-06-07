<?php

namespace KafkaBus\Messages\Tests;

use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Messages\DomainEventEnum;
use KafkaBus\Messages\Factories\DomainMessageFactory;
use KafkaBus\Workbench\ProductMessage;
use RdKafka\Message;
use Testo\Assert;
use Testo\Test;

class ProductMessageTest
{
    #[Test]
    public function create_domain_message_from_kafka(): void
    {
        $raw = [
            'event' => DomainEventEnum::Create->value,
            'attributes' => [
                'id' => 202410192253,
                'name' => 'Тестовый товар',
                'category' => [
                    'id' => 202410192254,
                    'name' => 'Тестовая категория',
                ],
                'attributes' => [
                    [
                        'id' => 202410192246,
                        'name' => 'Цвет',
                        'value' => 'Белый',
                    ],
                ],
            ],
            'dirty' => ['test'],
        ];

        $message = new Message();
        $message->payload = (string)json_encode($raw);

        $productDomainMessage = (new DomainMessageFactory(ProductMessage::class))
            ->fromKafka(new ConsumerMessage($message));

        Assert::equals($productDomainMessage->getEvent(), DomainEventEnum::Create);
        Assert::equals($productDomainMessage->getDirty(), ['test']);

        Assert::equals($productDomainMessage->id, 202410192253);
        Assert::equals($productDomainMessage->name, 'Тестовый товар');

        Assert::equals($productDomainMessage->category->id, 202410192254);
        Assert::equals($productDomainMessage->category->name, 'Тестовая категория');

        Assert::array($productDomainMessage->attributes)->hasCount(1);

        $attribute = $productDomainMessage->attributes[0];

        Assert::equals($attribute->id, 202410192246);
        Assert::equals($attribute->name, 'Цвет');
        Assert::equals($attribute->value, 'Белый');
    }
}
