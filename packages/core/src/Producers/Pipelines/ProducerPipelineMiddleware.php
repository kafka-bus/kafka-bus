<?php

namespace KafkaBus\Core\Producers\Pipelines;

use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;
use KafkaBus\Core\Producers\Messages\ProducerMessage;

/**
 * @extends PipelineMiddlewareInterface<ProducerMessage, ProducerPipelineHandler>
 */
interface ProducerPipelineMiddleware extends PipelineMiddlewareInterface
{
}
