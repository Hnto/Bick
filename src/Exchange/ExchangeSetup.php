<?php

namespace Bick\Exchange;

use Bick\Traits\ObjectAsArray;
use Bick\Traits\ObjectAsArrayInterface;

final class ExchangeSetup implements ObjectAsArrayInterface
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
    private $type;
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
     * ExchangeSetup constructor.
     *
     * @param string $name
     * @param array $props
     */
    public function __construct(string $name, array $props = [])
    {
        $this->name = $name;
        $this->passive = isset($props['passive']) ? $props['passive'] : false;
        $this->durable = isset($props['durable']) ? $props['durable'] : true;
        $this->type = isset($props['type']) ? $props['type'] : BickExchangeInterface::DEFAULT_EXCHANGE_TYPE;
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
    public function getType()
    {
        return $this->type;
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
