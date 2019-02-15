<?php

namespace Bick\Message;

use Bick\Traits\ClassConstructorProps;
use PhpAmqpLib\Message\AMQPMessage;

final class BickMessage implements BickMessageInterface
{
    use ClassConstructorProps;

    /**
     * @var array
     */
    private $body;

    /**
     * @var array
     */
    private $meta;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var string|null
     */
    private $batchUuid;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if (null === $this->exchange) {
            return false;
        }

        if (null === $this->routingKey) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return BickMessageInterface
     */
    public function setUuid(string $uuid): BickMessageInterface
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getBatchUuid(): ?string
    {
        return $this->batchUuid;
    }

    /**
     * @param string $batchUid
     * @return BickMessageInterface
     */
    public function setBatchUuid(string $batchUid): BickMessageInterface
    {
        $this->batchUuid = $batchUid;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRaw(): ?string
    {
        return json_encode([
            'body' => $this->body,
            'meta' => $this->meta,
            'published' => [
                'exchange' => $this->exchange,
                'routingKey' => $this->routingKey,
            ],
            'uuid' => $this->uuid,
            'batchUuid' => $this->batchUuid
        ]);
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ];
    }
}
