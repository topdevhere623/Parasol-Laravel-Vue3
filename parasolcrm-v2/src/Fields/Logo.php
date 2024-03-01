<?php

namespace ParasolCRMV2\Fields;

class Logo extends Upload
{
    /**
     * @var string
     */
    public string $component = 'LogoField';

    /** @var bool */
    public bool $displayOnTable = true;
}
