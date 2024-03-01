<?php

namespace App\View\Components;

use Illuminate\View\Component;
use NumberFormatter;

class TabbyCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public string $name,
        public int $paymentsCount = 0,
        public float $totalPrice = 0,
        public ?bool $isAvailable = true
    ) {
        $this->isAvailable ??= true;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $formatter = new NumberFormatter('en-US', NumberFormatter::SPELLOUT);
        $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET, '%spellout-ordinal');
        return view('components.tabby-card', compact('formatter'));
    }
}
