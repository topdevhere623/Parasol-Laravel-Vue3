<?php

namespace ParasolCRM\Fields;

use App\Models\Location;

class GoogleMap extends Field
{
    public string $component = 'GoogleMapField';

    public bool $displayOnTable = false;

    /** @var array */
    public array $attributes = [];

    // names of fields
    public string $latitudeName = 'lat';
    public string $longitudeName = 'lng';

    public function __construct($name, $label = null, $attrs = null)
    {
        parent::__construct($name, $label, $attrs);
        $this->withMeta([
            'searchLabel' => 'Search...',
            'latitudeName' => 'lat',
            'latitudeLabel' => 'Latitude',
            'longitudeName' => 'lng',
            'longitudeLabel' => 'Longitude',
        ]);
    }

    public function search($label): self
    {
        $this->withMeta(['searchLabel', $label]);
        return $this;
    }

    public function latitudeField(string $name, string $label = 'Latitude'): self
    {
        $this->latitudeName = $name;
        $this->withMeta(['latitudeName' => $name]);
        $this->withMeta(['latitudeLabel' => $label]);
        $this->attributes[] = $name;

        return $this;
    }

    public function longitudeField(string $name, string $label = 'Longitude'): self
    {
        $this->longitudeName = $name;
        $this->withMeta(['longitudeName' => $name]);
        $this->withMeta(['longitudeLabel' => $label]);
        $this->attributes[] = $name;

        return $this;
    }

    public function setFromRecord($record): self
    {
        $this->value = [
            'lat' => $record->{$this->latitudeName},
            'lng' => $record->{$this->longitudeName},
        ];

        return $this;
    }

    public function fillRecord($record): self
    {
        if ($this->fillableRecord) {
            $record->setAttribute($this->latitudeName, $this->value['lat'] ?? Location::DEFAULT_LATITUDE);
            $record->setAttribute($this->longitudeName, $this->value['lng'] ?? Location::DEFAULT_LONGITUDE);
        }

        return $this;
    }
}
