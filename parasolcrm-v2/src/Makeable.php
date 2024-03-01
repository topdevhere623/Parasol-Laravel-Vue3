<?php

namespace ParasolCRMV2;

trait Makeable
{
    /**
     * Create a new element.
     *
     * @return static
     */
    public static function make(...$arguments): self
    {
        return new static(...$arguments);
    }
}
