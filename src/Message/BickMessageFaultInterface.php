<?php

namespace Bick\Message;

/**
 * Interface BickMessageFault
 * The implementation of this interface
 * must also implement JsonSerializable
 *
 * @package Bick\Message
 */
interface BickMessageFaultInterface {

    /**
     * BickMessageFaultInterface constructor.
     *
     * @param mixed|string $message
     * @param int $code
     */
    public function __construct(?string $message, int $code);

    /**
     * @return array|mixed
     */
    public function jsonSerialize(): ?array;
}
