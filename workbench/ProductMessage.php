<?php

namespace KafkaBus\Workbench;

use KafkaBus\Messages\Data\Casters\CollectionCaster;
use KafkaBus\Messages\Data\Casters\PayloadCaster;
use KafkaBus\Messages\DomainMessage;
use KafkaBus\Workbench\Data\AttributePayload;
use KafkaBus\Workbench\Data\CategoryPayload;

/**
 * @property int $id
 * @property string $name
 * @property CategoryPayload $category
 * @property AttributePayload[] $attributes
 */
final class ProductMessage extends DomainMessage
{
    public function getKey(): ?string
    {
        return (string) $this->id;
    }

    protected function definitionCasters(): array
    {
        return [
            'category' => new PayloadCaster(CategoryPayload::class),
            'attributes' => new CollectionCaster(new PayloadCaster(AttributePayload::class)),
        ];
    }

    public static function factory(): ProductMessageTestFactory
    {
        return ProductMessageTestFactory::new();
    }
}
