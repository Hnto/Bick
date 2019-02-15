<?php

namespace Bick\Traits;

trait ObjectAsArray
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
