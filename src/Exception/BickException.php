<?php

namespace Bick\Exception;

use Throwable;

/**
 * Class BickException
 * @package Bick\Exception
 */
class BickException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
