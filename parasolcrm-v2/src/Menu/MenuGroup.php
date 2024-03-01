<?php

declare(strict_types=1);

namespace ParasolCRMV2\Menu;

use Closure;
use Illuminate\Support\Collection;
use ParasolCRMV2\Traits\Menu\WithIcon;

/**
 * @method static static make(Closure|string $label, iterable $items, string|null $divider = null, string|null $icon = null, Closure|bool|null $hasAccess = true)
 */
class MenuGroup extends MenuElement
{
    use WithIcon;

    protected ?string $divider = null;

    public function __construct(
        Closure|string $label,
        protected iterable $items,
        ?string $divider = null,
        string $icon = null,
        Closure|bool|null $hasAccess = true,
    ) {
        $this->setLabel($label);
        $this->setDivider($divider);
        $this->setItems($items);

        if ($icon) {
            $this->setIcon($icon);
        }

        if ($hasAccess !== null) {
            $this->setAccess($hasAccess);
        }
    }

    public function setItems(iterable $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function items(): Collection
    {
        return collect($this->items);
    }

    public function getDivider(): ?string
    {
        return $this->divider;
    }

    public function setDivider(?string $divider): void
    {
        $this->divider = $divider;
    }

    public function toArray(): array
    {
        $data = [
            'pages' => [
                [
                    'keenthemesIcon' => $this->getIcon(),
                    'sectionTitle' => $this->getLabel(),
                    'sub' => $this->items()->map->toArray(),
                ],
            ],
        ];

        if ($this->getDivider() !== null) {
            $data['heading'] = $this->getDivider();
        }

        return $data;
    }

}
