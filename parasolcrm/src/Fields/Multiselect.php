<?php

namespace ParasolCRM\Fields;

class Multiselect extends Field
{
    use RelationSelectable;
    use Selectable;

    public string $component = 'TagField';

    public bool $displayOnTable = false;

    protected string $storeAs = 'json';

    public function setFromRecord($record): self
    {
        $rawRecordValue = $this->getUncastedValue($record);

        $this->value = match ($this->getCast($record)) {
            'json' => json_decode($rawRecordValue),
            'array' => unserialize($rawRecordValue),
            'string' => $rawRecordValue ? explode(',', $rawRecordValue) : []
        };

        return $this;
    }

    public function fillRecord($record): self
    {
        $record->setRawAttributes(
            array_merge($record->getAttributes(), [
                $this->name => $this->value ? match ($this->getCast($record)) {
                    'json' => json_encode($this->value),
                    'array' => serialize($this->value),
                    'string' => implode(',', $this->value),
                    default => null
                } : $this->value,
            ])
        );

        return $this;
    }

    public function storeAs(string $storeAs): self
    {
        $this->storeAs = $storeAs;
        return $this;
    }

    protected function getCast($record): string
    {
        $recordCasts = $record->getCasts();
        return key_exists($this->name, $recordCasts) ? $recordCasts[$this->name] : $this->storeAs;
    }

    protected function getUncastedValue($record): ?string
    {
        $recordCasts = $record->getCasts();
        return key_exists($this->name, $recordCasts) ? $record->getRawOriginal($this->name) : $record->{$this->name};
    }
}
