<?php

declare(strict_types=1);

namespace KafkaBus\Partitions;

enum Offset
{
    case Early;
    case Latest;
}
