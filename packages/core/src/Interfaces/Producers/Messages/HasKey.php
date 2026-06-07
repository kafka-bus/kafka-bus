<?php

namespace KafkaBus\Core\Interfaces\Producers\Messages;

interface HasKey
{
    public function getKey(): ?string;
}
