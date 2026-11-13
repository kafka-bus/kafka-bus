<?php

declare(strict_types=1);

namespace KafkaBus\TypedTopics;

enum UnroutableReason: string
{
    case MissingHeader = 'missing_header';
    case UnknownType = 'unknown_type';
}
