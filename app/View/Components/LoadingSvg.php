<?php

namespace App\View\Components;

use Illuminate\View\Component;

class LoadingSvg extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $color = '#fff',
        public string $width = '20px',
        public string $height = '20px'
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.loading-svg');
    }
}
