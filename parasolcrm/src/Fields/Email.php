<?php

namespace ParasolCRM\Fields;

class Email extends Field
{
    public string $component = 'EmailField';

    public function __construct($name, $label = null, $attrs = null)
    {
        parent::__construct($name, $label, $attrs);
        $this->rules([]);
        $this->url("mailto:{{$this->column}}");
    }

    public function rules($rules): Field
    {
        parent::rules($rules);
        $this->rules = array_merge(['email', 'nullable'], $this->rules);
        return $this;
    }
}
