<?php

namespace ParasolCRM\Statuses;

class TextStatus extends Status
{
    public string $component = 'StatusText';

    public function __construct(string $title, $callback)
    {
        parent::__construct($title);
        $this->dataValue($callback);
    }

    public function icon(string $icon): self
    {
        $this->withMeta(['icon' => $icon]);
        return $this;
    }

    public function variant(string $variant): self
    {
        $this->withMeta(['variant' => $variant]);
        return $this;
    }
}
