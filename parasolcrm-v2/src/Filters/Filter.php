<?php

namespace ParasolCRMV2\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use ParasolCRMV2\Filters\Fields\FilterField;
use ParasolCRMV2\HasAccessCallback;
use ParasolCRMV2\Makeable;
use ParasolCRMV2\Metable;

/**
 * Class Filter
 *
 * @package ParasolCRMV2\Filters
 */
abstract class Filter implements \JsonSerializable
{
    use Makeable;
    use Metable;
    use HasAccessCallback;

    /**
     * Name of the search field
     */
    public string $name;

    /**
     * Filter column name
     */
    public string $column;

    /**
     * Filter field component
     */
    public FilterField $field;

    /**
     * Display name
     */
    public string $label;

    /**
     * Display filter along with table
     */
    public bool $isQuick = false;

    /**
     * Hide field from layout. But accept for apply
     */
    public bool $isHidden = false;

    protected ?Closure $applyHandlerCallback = null;

    /**
     * Filter constructor.
     */
    public function __construct(FilterField $field, string $name, string $column = null, string $label = null)
    {
        $this->field = $field;
        $this->column = $column ?? $name;
        $this->name = $name;
        $this->label = $label ?? str_replace(['.', '_'], ' ', trim(ucfirst($name)));
    }

    /**
     * Filter handler
     */
    abstract public function apply(Builder $builder, $value): void;

    public function applyHandler(Closure $callback): self
    {
        $this->applyHandlerCallback = $callback;

        return $this;
    }

    public function resolveApply(Builder $builder, $value): self
    {
        if (!is_null($this->applyHandlerCallback)) {
            call_user_func($this->applyHandlerCallback, $builder, $value);
        } else {
            $this->apply($builder, $value);
        }

        return $this;
    }

    /**
     * Display filter along with table
     */
    final public function quick(): self
    {
        $this->isQuick = true;

        return $this;
    }

    /**
     * Hide field from layout. But accept for apply
     *
     */
    final public function hidden($isHidden = true): self
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    public function getDefaultValue()
    {
        return $this->field->getDefaultValue();
    }

    /**
     * Prepare field for JSON serialization.
     *
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->field->jsonSerialize(),
            [
                'name' => $this->name,
                'label' => $this->label,
                'isQuick' => $this->isQuick,
            ],
            $this->getMeta()
        );
    }
}
