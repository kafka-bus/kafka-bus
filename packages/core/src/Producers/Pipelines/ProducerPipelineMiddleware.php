<?php

namespace KafkaBus\Core\Producers\Pipelines;

use KafkaBus\Core\Interfaces\Pipelines\PipelineMiddlewareInterface;

/**
 * @extends PipelineMiddlewareInterface<ProducerPipelineHandler>
 */
interface ProducerPipelineMiddleware extends PipelineMiddlewareInterface
{
}
