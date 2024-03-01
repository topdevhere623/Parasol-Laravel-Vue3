<?php

declare(strict_types=1);

namespace ParasolCRMV2\Menu;

use Closure;
use ParasolCRMV2\Makeable;
use ParasolCRMV2\Traits\Menu\WithAccess;

abstract class MenuElement
{
    use Makeable;
    use WithAccess;

    protected Closure|string $label = '';

    abstract public function toArray(): array;

    public function isGroup(): bool
    {
        return $this instanceof MenuGroup;
    }

    public function label(): string
    {
        $this->label = $this->label instanceof Closure
            ? call_user_func($this->label)
            : $this->label;

        return $this->label;
    }

    public function setLabel(Closure|string $label): static
    {
        $this->label = $label;

        return $this;
    }

  public function getLabel(): Closure|string
  {
      return $this->label;
  }
}
