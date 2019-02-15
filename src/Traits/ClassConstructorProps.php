<?php

namespace Bick\Traits;

trait ClassConstructorProps
{
    public function __construct(array $info)
    {
        foreach ($info as $key => $value) {
            if (!property_exists(get_class($this), $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }
}