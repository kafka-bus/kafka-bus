<?php

namespace KafkaBus\Core\Interfaces\Producers\Messages;

interface HasPartition
{
    public function getPartition(): int;
}
