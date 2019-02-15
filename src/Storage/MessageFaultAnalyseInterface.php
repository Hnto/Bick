<?php

namespace Bick\Storage;

use Bick\Message\BickMessageFaultInterface;
use Bick\Message\BickMessageInterface;

/**
 * Interface MessageFaultAnalyseInterface
 * @package Bick\Interfaces
 */
interface MessageFaultAnalyseInterface
{
    /**
     * @param BickMessageInterface $message
     * @param BickMessageFaultInterface $fault
     */
    public function analyse(BickMessageInterface $message, BickMessageFaultInterface $fault): void;
}
