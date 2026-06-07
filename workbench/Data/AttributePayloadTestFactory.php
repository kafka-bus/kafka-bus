<?php

namespace KafkaBus\Workbench\Data;


use KafkaBus\Messages\Testing\PayloadTestFactory;

/**
 * @extends PayloadTestFactory<AttributePayload>
 */
final class AttributePayloadTestFactory extends PayloadTestFactory
{
    protected string $payloadClass = AttributePayload::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(),
            'name' => $this->faker->word,
            'value' => $this->faker->word,
        ];
    }
}
