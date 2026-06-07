<?php

namespace KafkaBus\Workbench;

use KafkaBus\Messages\Testing\DomainMessageTestFactory;
use KafkaBus\Workbench\Data\AttributePayloadTestFactory;
use KafkaBus\Workbench\Data\CategoryPayloadTestFactory;

/**
 * @extends DomainMessageTestFactory<ProductMessage>
 */
final class ProductMessageTestFactory extends DomainMessageTestFactory
{
    protected string $messageClass = ProductMessage::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(),
            'name' => $this->faker->sentence(),
            'category' => CategoryPayloadTestFactory::new()->makeArray(),
            'attributes' => [
                AttributePayloadTestFactory::new()
                    ->makeArray(),
            ],
        ];
    }
}
