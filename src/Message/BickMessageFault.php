<?php

namespace Bick\Message;

/**
 * Class BickMessageFault
 * @package Bick\Message
 */
final class BickMessageFault implements \JsonSerializable, BickMessageFaultInterface
{
    /**
     * @var array|string
     */
    private $message;

    /**
     * @var int
     */
    private $code;

    /**
     * BickMessageFault constructor.
     * @param string|array|null $message
     * @param int $code
     */
    public function __construct($message, int $code)
    {
        if (is_string($message) || is_array($message)) {
            $this->message = $message;
        } else {
            $this->message = null;
        }

        $this->code = $code;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize(): ?array
    {
        return [
            'message' => $this->message,
            'code' => $this->code
        ];
    }
}
