<?php

namespace ParasolCRMV2\Fields;

class Avatar extends Upload
{
    /**
     * @var string
     */
    public string $component = 'AvatarField';

    /** @var bool */
    public bool $displayOnTable = true;

    /**
     * @param string $name
     * @return $this
     */
    public function username(string $name): self
    {
        $this->dependsOn($name, null, []);
        return $this;
    }
}
