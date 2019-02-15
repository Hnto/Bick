<?php

namespace Bick\Message;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * This interface can be used to implement
 * your own translation strategy in order
 * to modify AMQPMessage objects when not using
 * the default BickMessage object
 *
 * Interface BickMessageTranslatorInterface
 * @package Bick\Message
 */
interface BickMessageTranslatorInterface
{
    /**
     * This method must translate
     * a received AMQPMessage object
     * and return a BickMessageInterface
     *
     * @param AMQPMessage $msg
     * @return BickMessageInterface
     */
    public function translate(AMQPMessage $msg): BickMessageInterface;
}
