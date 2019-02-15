<?php

namespace Bick\Message;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class BickMessageTranslator
 * @package Bick\Message
 */
class BickMessageTranslator implements BickMessageTranslatorInterface
{
    /**
     * @param AMQPMessage $msg
     * @return BickMessageInterface
     */
    public function translate(AMQPMessage $msg): BickMessageInterface
    {
        $contentType = isset($msg->get_properties()['content_type']) ?: null;

        $msgBody = null;
        switch ($contentType) {
            case 'application/json':
                $msgBody  = json_decode($msg->body, true);
                break;
            default:
                $msgBody['body'] = $msg->body;
                break;
        }

        return new BickMessage([
            'body' => isset($msgBody['body']) ? $msgBody['body'] : null,
            'meta' => isset($msgBody['meta']) ? $msgBody['meta'] : null,
            'exchange' => isset($msg->delivery_info['exchange']) ? $msg->delivery_info['exchange'] : null,
            'routingKey' => isset($msg->delivery_info['routing_key']) ? $msg->delivery_info['routing_key'] : null,
            'uuid' => isset($msgBody['uuid']) ? $msgBody['uuid'] : null,
            'batchUuid' => isset($msgBody['batchUuid']) ? $msgBody['batchUuid'] : null,
        ]);
    }
}