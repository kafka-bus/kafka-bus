<?php

namespace KafkaBus\Workbench\Data;

use KafkaBus\Messages\Data\Payload;

/**
 * @property int $id
 * @property string $name
 * @property string $value
 */
final class AttributePayload extends Payload
{
    public static function factory(): AttributePayloadTestFactory
    {
        return AttributePayloadTestFactory::new();
    }
}
