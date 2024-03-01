<?php

namespace ParasolCRMV2\Fields;

trait RelationSelectable
{
    protected string $valueField = 'id';
    protected string $titleField = 'title';

    public function valueField(string $valueField = 'id')
    {
        $this->valueField = $valueField;
        return $this;
    }

    public function titleField(string $titleField = 'title')
    {
        $this->titleField = $titleField;
        return $this;
    }
    public function getValueField()
    {
        return $this->valueField;
    }

    public function getTitleField()
    {
        return $this->titleField;
    }
}
