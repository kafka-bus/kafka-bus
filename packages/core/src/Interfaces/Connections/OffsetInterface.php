<?php

namespace KafkaBus\Core\Interfaces\Connections;

interface OffsetInterface
{
    public function toValue(): int|string;
}
