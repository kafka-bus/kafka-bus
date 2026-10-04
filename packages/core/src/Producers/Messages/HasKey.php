<?php

namespace KafkaBus\Core\Producers\Messages;

interface HasKey
{
    public function getKey(): ?string;
}
