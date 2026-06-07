<?php

namespace KafkaBus\Workbench\Data;

use KafkaBus\Messages\Data\Payload;

/**
 * @property int $id
 * @property string $name
 */
final class CategoryPayload extends Payload
{
    public static function factory(): CategoryPayloadTestFactory
    {
        return CategoryPayloadTestFactory::new();
    }
}
