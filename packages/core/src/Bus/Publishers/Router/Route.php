<?php

namespace KafkaBus\Core\Bus\Publishers\Router;

use KafkaBus\Core\Interfaces\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Topics\Topic;

/**
 * @template TMessage of ProducerMessageInterface = mixed
 */
readonly class Route
{
    /**
     * @param class-string<TMessage> $messageClass
     * @param Topic $topic
     * @param Options $options
     */

    public function __construct(
        public string  $messageClass,
        public Topic   $topic,
        public Options $options = new Options()
    ) {
    }
}
