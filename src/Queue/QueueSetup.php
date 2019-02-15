<?php

namespace Bick\Queue;

use Bick\Traits\ObjectAsArray;
use Bick\Traits\ObjectAsArrayInterface;

final class QueueSetup implements ObjectAsArrayInterface
{
    use ObjectAsArray;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool|mixed
     */
    private $passive;
    /**
     * @var bool|mixed
     */
    private $durable;
    /**
     * @var bool|mixed
     */
    private $exclusive;
    /**
     * @var bool|mixed
     */
    private $autoDelete;
    /**
     * @var bool|mixed
     */
    private $noWait;
    /**
     * @var array|mixed
     */
    private $arguments;
    /**
     * @var mixed|null
     */
    private $ticket;

    /**
     * QueueSetup constructor.
     *
     * @param string $name
     * @param array $props
     */
    public function __construct(string $name, array $props = [])
    {
        $this->name = $name;
        $this->passive = isset($props['passive']) ? $props['passive'] : false;
        $this->durable = isset($props['durable']) ? $props['durable'] : true;
        $this->exclusive = isset($props['exclusive']) ? $props['exclusive'] : false;
        $this->autoDelete = isset($props['autoDelete']) ? $props['autoDelete'] : false;
        $this->noWait = isset($props['nowait']) ? $props['nowait'] : false;
        $this->arguments = isset($props['arguments']) ? $props['arguments'] : [];
        $this->ticket = isset($props['ticket']) ? $props['ticket'] : null;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool|mixed
     */
    public function getPassive()
    {
        return $this->passive;
    }

    /**
     * @return bool|mixed
     */
    public function getDurable()
    {
        return $this->durable;
    }

    /**
     * @return bool|mixed
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * @return bool|mixed
     */
    public function getAutoDelete()
    {
        return $this->autoDelete;
    }

    /**
     * @return bool|mixed
     */
    public function getNoWait()
    {
        return $this->noWait;
    }

    /**
     * @return array|mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return mixed|null
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
