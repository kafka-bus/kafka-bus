<?php

namespace KafkaBus\Core\Receivers\Pipelines;

use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Core\Pipelines\PipelineMiddlewareInterface;

/**
 * @extends PipelineMiddlewareInterface<ConsumerMessageInterface, ReceiverPipelineHandler>
 */
interface ReceiverPipelineMiddleware extends PipelineMiddlewareInterface
{
}
