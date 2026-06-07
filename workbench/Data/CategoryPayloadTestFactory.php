<?php

namespace KafkaBus\Workbench\Data;

use KafkaBus\Messages\Testing\PayloadTestFactory;

/**
 * @extends PayloadTestFactory<CategoryPayload>
 */
final class CategoryPayloadTestFactory extends PayloadTestFactory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(),
            'name' => $this->faker->word(),
        ];
    }
}
