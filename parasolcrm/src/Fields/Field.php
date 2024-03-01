<?php

namespace ParasolCRM\Fields;

use Closure;
use Illuminate\Database\Eloquent\Model;
use ParasolCRM\HasAccessCallback;
use ParasolCRM\Makeable;
use ParasolCRM\Metable;

abstract class Field implements \JsonSerializable
{
    use Makeable;
    use Metable;
    use HasAccessCallback;

    public string $component;

    public string $label;

    public string $name;

    public mixed $value = null;

    // Validation rules

    public array $rules = [];

    public array $creationRules = [];

    public array $updateRules = [];

    protected string $actionType = 'create';

    // Table properties

    public string $column;

    public bool|string $sortable = false;

    public bool $shownOnTable = true;

    // Properties for setting the display of a field on different pages

    public bool $displayOnTable = true;

    public bool $displayOnForm = true;

    // Field attrivutes
    public ?array $badges = null;

    public ?array $attrs = null;

    public array $depends = [];

    public bool $dependentBehavior = false;

    public bool $nullable = false;

    public $nullValues = ['', null];

    protected $url;

    // Value manipulation handlers
    protected bool $fillableRecord = true;

    protected ?Closure $setValueHandlerCallback = null;

    protected ?Closure $setFromRecordHandlerCallback = null;

    protected ?Closure $fillRecordHandlerCallback = null;

    protected ?Closure $displayHandlerCallback = null;

    public $default = null;

    public bool $isComputed = false;

    public function __construct($name, $label = null, $attrs = null)
    {
        $this->name = $name;

        $label ??= \Str::of($name)->camel()->snake(' ');
        $this->label(ucfirst($label));

        $this->attrs = $attrs;

        $this->column = $name;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function column($column): self
    {
        $this->column = $column;

        return $this;
    }

    public function setAttrs(array $attrs): self
    {
        $this->attrs = $attrs;

        return $this;
    }

    // Value manipulations

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setValueHandler(Closure $callback): self
    {
        $this->setValueHandlerCallback = $callback;

        return $this;
    }

    public function resolveSetValue($value, $record): self
    {
        if (!is_null($this->setValueHandlerCallback)) {
            $this->value = call_user_func($this->setValueHandlerCallback, $value, $this, $record);
        } else {
            $this->setValue($value);
        }

        if ($this->isNullValue($this->value)) {
            $this->value = null;
        }

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValueToDefault(): self
    {
        $this->value = $this->default;

        return $this;
    }

    // Can't use return type declaration because of Selectable field in child component
    // TODO: Rewrite Selectable Trait to Selectable Class
    public function setFromRecord($record)
    {
        $this->value = $record->{$this->column};

        return $this;
    }

    public function setFromRecordHandler(Closure $callback): self
    {
        $this->setFromRecordHandlerCallback = $callback;

        return $this;
    }

    public function resolveSetFromRecord(Model $record): self
    {
        if (!is_null($this->setFromRecordHandlerCallback)) {
            $this->value = call_user_func($this->setFromRecordHandlerCallback, $record, $this);
        } else {
            $this->setFromRecord($record);
        }

        return $this;
    }

    public function fillRecord(Model $record): self
    {
        $record->setAttribute($this->column, $this->value);

        return $this;
    }

    public function fillRecordHandler(Closure $callback): self
    {
        $this->fillRecordHandlerCallback = $callback;

        return $this;
    }

    public function resolveFillRecord(Model $record): self
    {
        if ($this->fillableRecord) {
            if (!is_null($this->fillRecordHandlerCallback)) {
                call_user_func($this->fillRecordHandlerCallback, $record, $this);
            } else {
                $this->fillRecord($record);
            }
        }

        return $this;
    }

    public function unfillableRecord(): self
    {
        $this->fillableRecord = false;

        // TODO: Обсудить правильность этого свойства здесь
        // $this->displayOnTable = false;

        return $this;
    }

    public function displayValue($record)
    {
        return $record->{$this->column};
    }

    public function displayHandler(Closure $callback): self
    {
        $this->displayHandlerCallback = $callback;

        return $this;
    }

    public function resolveDisplayValue($record)
    {
        $result = [];

        if (!is_null($this->displayHandlerCallback)) {
            $content = call_user_func($this->displayHandlerCallback, $record, $this);
        } else {
            $content = $this->displayValue($record);
        }

        if ($this->badges) {
            $this->setFromRecord($record);
            $result['value'] = $this->value;
        }

        if ($this->url) {
            $result['url'] = $this->getUrl($record);
        }

        if ($result) {
            $result['text'] = $content;

            return $result;
        }

        return $content;
    }

    public function getUrl($record)
    {
        if (is_null($this->url)) {
            return;
        }

        if (is_callable($this->url)) {
            return call_user_func($this->url, $record, $this) ?? null;
        } elseif (is_string($this->url)) {
            $recordValues = $record->toArray();

            return strtr(
                $this->url,
                array_combine(
                    array_map(fn ($keys) => "{{$keys}}", array_keys($recordValues)),
                    $recordValues
                ) ?? null
            );
        }

        return $this->url;
    }

    // Validation Rules

    public function actionTypeUpdate()
    {
        $this->actionType = 'update';

        return $this;
    }

    public function actionTypeCreate()
    {
        $this->actionType = 'create';

        return $this;
    }

    public function getRules(string $type): array
    {
        $rules = $this->rules;

        switch ($type) {
            case 'create':
                $rules = array_merge($rules, $this->creationRules);

                break;
            case 'update':
                $rules = array_merge($rules, $this->updateRules);

                break;
        }

        return $rules;
    }

    public function rules($rules): self
    {
        $this->rules = is_array($rules) ? $rules : func_get_args();

        return $this;
    }

    public function creationRules($rules): self
    {
        $this->creationRules = is_array($rules) ? $rules : func_get_args();

        return $this;
    }

    public function updateRules($rules): self
    {
        $this->updateRules = is_array($rules) ? $rules : func_get_args();

        return $this;
    }

    // Methods for setting the display of a field on different pages

    public function hideOnTable(): self
    {
        $this->shownOnTable = false;

        return $this;
    }

    public function showOnTable()
    {
        $this->shownOnTable = true;

        return $this;
    }

    public function onlyOnTable(): self
    {
        $this->displayOnTable = true;
        $this->displayOnForm = false;

        return $this;
    }

    public function onlyOnForm(): self
    {
        $this->displayOnForm = true;
        $this->displayOnTable = false;

        return $this;
    }

    public function badges($badges)
    {
        $this->badges = $badges;

        return $this;
    }

    public function tooltip($text, $isHtml = false): self
    {
        if ($text) {
            $this->withMeta(['tooltip' => compact('text', 'isHtml')]);
        }

        return $this;
    }

    // behavior = ['show', 'hide', 'disable', 'set' => 100]
    public function dependsOn($field, $value, $behaviors = ['show']): self
    {
        $this->depends = [
            'field' => $field,
            'value' => $value,
        ];

        // Формируем массив для удобной работы с объектом на фронте
        foreach ($behaviors as $k => $v) {
            if (is_string($k)) {
                $this->depends[$k] = $v;
            } else {
                $this->depends[$v] = true;
            }
        }

        return $this;
    }

    public function dependentBehavior(): self
    {
        $this->dependentBehavior = true;

        return $this;
    }

    /**
     * If $column is not set then $this->sortable is concatenation of Resource $model table name and this $name
     */
    public function sortable(string|bool $column = true): self
    {
        $this->sortable = $column;

        return $this;
    }

    public function placeholder($text): self
    {
        $this->withMeta(['placeholder' => $text]);

        return $this;
    }

    /**
     * Setting a default value for the created item
     */
    public function default($value): self
    {
        $this->default = $value;

        return $this;
    }

    public function computed(): self
    {
        $this->isComputed = true;

        return $this;
    }

    public function nullable($nullable = true, $values = null): self
    {
        $this->nullable = $nullable;

        if ($values !== null) {
            $this->nullValues($values);
        }

        return $this;
    }

    public function nullValues($values): self
    {
        $this->nullValues = $values;

        return $this;
    }

    protected function isNullValue($value): bool
    {
        if (!$this->nullable) {
            return false;
        }

        return is_callable($this->nullValues)
            ? ($this->nullValues)($value)
            : in_array($value, (array)$this->nullValues, true);
    }

    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            [
                'component' => $this->component,
                'label' => $this->label,
                'name' => $this->name,
                'attrs' => $this->attrs,
                'rules' => $this->getRules($this->actionType),
                'value' => $this->value,
                'sortable' => $this->sortable,
                'showOnTable' => $this->shownOnTable,
                'badges' => $this->badges,
                'depends' => $this->depends,
                'dependentBehavior' => $this->dependentBehavior,
                'nullable' => $this->nullable,
            ],
            $this->getMeta()
        );
    }
}
