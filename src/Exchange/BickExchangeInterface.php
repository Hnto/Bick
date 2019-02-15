<?php

namespace Bick\Exchange;

interface BickExchangeInterface
{
    public const DEFAULT_EXCHANGE_TYPE = self::DIRECT_EXCHANGE;

    public const DIRECT_EXCHANGE = 'direct';
    public const FANOUT_EXCHANGE = 'fanout';
}