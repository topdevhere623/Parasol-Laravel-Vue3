<?php

namespace ParasolCRM\Fields;

use Closure;
use Illuminate\Support\Collection;

trait Selectable
{
    public array $options = [];
    public bool $multiple = false;
    public bool $autocomplete = false;

    protected ?string $endpoint = null;

    protected $formOptions;

    public function options(array|Collection $options): self
    {
        $newOptions = [];
        foreach ($options as $index => $option) {
            if (is_array($option)) {
                $newOptions[] = [
                    'value' => "{$index}",
                    ...$option,
                ];
            } else {
                $newOptions[] = [
                    'value' => "{$index}",
                    'text' => $option,
                ];
            }
        }

        $this->options = $newOptions;
        $this->withMeta(['options' => $this->options]);

        return $this;
    }

    public function multiple(): self
    {
        $this->multiple = true;

        $this->withMeta(['multiple' => true]);

        return $this;
    }

    public function autocomplete(): self
    {
        $this->autocomplete = true;

        $this->withMeta(['autocomplete' => true]);

        return $this;
    }

    public function displayValue($record)
    {
        foreach ($this->options as $option) {
            $recordValue = $record->{$this->column};
            if ($record->{$this->column} instanceof \UnitEnum) {
                $recordValue = $record->{$this->column}->value;
            }

            if ($option['value'] == $recordValue) {
                return $option['text'];
            }
        }
    }

    public function formOptions($callback): self
    {
        $this->formOptions = $callback;

        return $this;
    }

    public function setFromRecord($record)
    {
        $this->value = $record->{$this->column};

        // Set default options
        if (!is_null($this->formOptions)) {
            if ($this->formOptions instanceof Closure) {
                $this->options = call_user_func($this->formOptions, $record, $this);
            } else {
                $this->options = $this->formOptions;
            }
            $this->withMeta(['options' => $this->options]);
        }

        return $this;
    }

    public function endpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        $this->withMeta([
            'endpoint' => $this->endpoint,
        ]);

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }
}
