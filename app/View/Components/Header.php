<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Header extends Component
{
    public function __construct(public ?string $subtitle = null, public ?bool $sm = false, public ?string $tag = 'h2')
    {
        //
    }

    public function render()
    {
        return view('components.header');
    }
}
