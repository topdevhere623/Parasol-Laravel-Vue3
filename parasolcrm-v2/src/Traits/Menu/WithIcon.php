<?php

declare(strict_types=1);

namespace ParasolCRMV2\Traits\Menu;

use Closure;

trait WithIcon
{
    protected Closure|string $icon = 'element-11';

    public function getIcon(): Closure|string
    {
        return is_callable($this->icon) ? call_user_func($this->icon) : $this->icon;
    }

    public function setIcon(Closure|string $icon): void
    {
        $this->icon = $icon;
    }
}
