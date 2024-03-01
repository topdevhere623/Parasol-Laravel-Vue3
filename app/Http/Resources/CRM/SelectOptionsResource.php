<?php

namespace App\Http\Resources\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @var Model */
class SelectOptionsResource extends JsonResource
{
    protected string $keyField = 'id';
    protected string $valueField = 'name';

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'key' => $this->{$this->keyField},
            'value' => $this->{$this->valueField},
        ];
    }

    public function setFields(string $valueField, ?string $keyField = null): self
    {
        $this->keyField = $keyField ?? $this->keyField;
        $this->valueField = $valueField;

        return $this;
    }
}
