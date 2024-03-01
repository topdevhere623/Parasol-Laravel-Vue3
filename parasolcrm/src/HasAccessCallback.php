<?php

namespace ParasolCRM;

trait HasAccessCallback
{
    public $hasAccessCallback = null;

    public function checkHasAccess(...$args): bool
    {
        if (is_bool($this->hasAccessCallback)) {
            return $this->hasAccessCallback;
        }
        return $this->hasAccessCallback ? call_user_func($this->hasAccessCallback, ...$args) : true;
    }

    public function hasAccess($callback): self
    {
        $this->hasAccessCallback = $callback;

        return $this;
    }
}
