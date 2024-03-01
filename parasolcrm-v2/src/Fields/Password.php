<?php

namespace ParasolCRMV2\Fields;

use Illuminate\Support\Facades\Hash;

class Password extends Field
{
    public string $component = 'PasswordField';

    public bool $displayOnTable = false;

    public function setValue($value): self
    {
        if (!is_null($value)) {
            parent::setValue(Hash::make($value));
        }

        return $this;
    }

    public function fillRecord($record): self
    {
        if (!is_null($this->value)) {
            parent::fillRecord($record);
        }

        return $this;
    }

    public function setFromRecord($record): self
    {
        return $this;
    }

    public function getValue()
    {
    }
}
