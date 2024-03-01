<?php

namespace ParasolCRM\Containers;

use Illuminate\Support\Str;
use ParasolCRM\HasAccessCallback;
use ParasolCRM\Makeable;
use ParasolCRM\Metable;

abstract class Container implements \JsonSerializable
{
    use Makeable;
    use Metable;
    use HasAccessCallback;

    public string $component;
    public string $name;
    public string $label;

    public array $children = [];
    public ?array $depends = null;
    public ?array $attrs = null;

    public function __construct(string $label = '', array $attrs = null)
    {
        $this->label = $label;
        $this->name = $label ? Str::snake($label) : Str::random(10);
        $this->attrs = $attrs;
    }

    public function attach($array = []): self
    {
        $this->children = array_merge($this->children, $array);

        return $this;
    }

    public function dependsOn($field, $value): self
    {
        $this->depends = [
            'field' => $field,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Prepare field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'component' => $this->component,
            'name' => $this->name,
            'label' => $this->label,
            'children' => $this->children,
            'depends' => $this->depends,
            'attrs' => $this->attrs,
        ], $this->getMeta());
    }
}
