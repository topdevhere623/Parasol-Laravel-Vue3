<?php

namespace ParasolCRMV2\Traits\Menu;

trait WithAccess
{
    protected Closure|bool|null $hasAccess = null;
    private string $permission = '';
    public function setAccess(bool $value): static
    {
        $this->hasAccess = $value;

        return $this;
    }

    public function setPermission(string $value): static
    {
        $this->permission = $value;

        return $this;
    }

    public function getHasAccess(): bool|null
    {
        return is_callable($this->hasAccess) ? call_user_func($this->hasAccess) : $this->hasAccess;
    }

    public function getPermission(): string
    {
        if (empty($this->permission) && method_exists($this, 'getFiller') && class_exists($this->getFiller())) {
            return 'index-'.$this->getFiller()::$model;
        }

        return $this->permission;
    }
}
