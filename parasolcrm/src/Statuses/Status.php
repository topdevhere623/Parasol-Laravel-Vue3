<?php

namespace ParasolCRM\Statuses;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRM\HasAccessCallback;
use ParasolCRM\Makeable;
use ParasolCRM\Metable;

class Status implements \JsonSerializable
{
    use Makeable;
    use Metable;
    use HasAccessCallback;

    public string $component = 'Status';
    public string $title;
    public $data;
    public $dataValue;
    protected ?Closure $query = null;
    protected ?Closure $executor = null;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function hint($text): self
    {
        $this->withMeta(['hint' => $text]);
        return $this;
    }

    public function resolveData(Builder $builder): self
    {
        if ($this->query && is_callable($this->query)) {
            $builder = call_user_func($this->query, $builder);
        }
        if (is_callable($this->dataValue)) {
            $this->data = call_user_func($this->dataValue, $builder->clone());
        } else {
            $this->data = $this->dataValue;
        }

        return $this;
    }

    public function query(Closure $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function dataValue($callback)
    {
        $this->dataValue = $callback;
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'component' => $this->component,
            'title' => $this->title,
            'data' => $this->data,
        ], $this->getMeta());
    }
}
