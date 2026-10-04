<?php

namespace KafkaBus\Core\Publishers\Pipelines;

use KafkaBus\Core\Pipelines\PipelineMiddlewareInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;

/**
 * @extends PipelineMiddlewareInterface<ProducerMessage, PublisherPipelineHandler>
 */
interface PublisherPipelineMiddleware extends PipelineMiddlewareInterface
{
}
