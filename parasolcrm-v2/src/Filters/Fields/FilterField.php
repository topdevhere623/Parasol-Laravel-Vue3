<?php

namespace ParasolCRMV2\Filters\Fields;

use ParasolCRMV2\Makeable;
use ParasolCRMV2\Metable;

/**
 * Class FilterField
 *
 * @package ParasolCRMV2\Filters\Fields
 */
abstract class FilterField implements \JsonSerializable
{
    use Makeable;
    use Metable;

    /**
     * Front component name
     *
     * @var string
     */
    protected string $component = '';

    protected $value;
    protected $defaultValue;

    public function __construct($defaultValue = null)
    {
        if ($defaultValue !== null) {
            $this->default($defaultValue);
        }
    }

    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function default($value)
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Prepare field for JSON serialization.
     *
     * @return array
     */
    final public function jsonSerialize(): array
    {
        return array_merge([
            'component' => $this->component,
        ], $this->getMeta());
    }
}
