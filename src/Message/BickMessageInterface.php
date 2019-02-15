<?php

namespace Bick\Message;

/**
 * Interface BickMessageInterface
 *
 * @package Bick
 */
interface BickMessageInterface
{
    public const MESSAGE_ACK = 1;
    public const MESSAGE_NACK = 0;
    public const MESSAGE_NACK_REQUEUE = -1;

    public const STATUS_BUSY = 0;
    public const STATUS_DONE = 1;

    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @return string
     */
    public function getExchange(): string;

    /**
     * @return string
     */
    public function getRoutingKey(): string;

    /**
     * @return mixed
     */
    public function getRaw(): ?string;

    /**
     * @return array
     */
    public function getProperties(): array;
}
