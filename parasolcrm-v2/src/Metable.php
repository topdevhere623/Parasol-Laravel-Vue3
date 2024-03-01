<?php

namespace ParasolCRMV2;

trait Metable
{
    /**
     * The meta data for the element.
     *
     * @var array
     */
    protected array $meta = [];

    /**
     * Get additional meta information to merge with the element payload.
     *
     * @return array
     */
    protected function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Set additional meta information for the element.
     *
     * @param  array  $meta
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }
}
