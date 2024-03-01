<?php

declare(strict_types=1);

namespace ParasolCRMV2\Menu;

use Closure;

use ParasolCRMV2\Contracts\Menu\MenuFiller;
use ParasolCRMV2\Traits\Menu\WithIcon;
use Throwable;

/**
 * @method static make(Closure|string $label, Closure|MenuFiller|string $filler, string $icon = null, Closure|bool|null $hasAccess = null, string|null $permission = null)
 */
class MenuItem extends MenuElement
{
    use WithIcon;

    protected Closure|string|null $url = null;

    protected ?Closure $badge = null;

    /**
     * @param Closure|string $label
     * @param Closure|MenuFiller|string $filler
     * @param string|null $icon
     * @param string|null $permission
     * @param Closure|bool|null $hasAccess
     */
    final public function __construct(
        Closure|string $label,
        protected Closure|MenuFiller|string $filler,
        string $icon = null,
        Closure|bool|null $hasAccess = null,
        string|null $permission = null,
    ) {
        $this->setLabel($label);

        if ($icon) {
            $this->setIcon($icon);
        }

        if ($hasAccess !== null) {
            $this->setAccess($hasAccess);
        }

        if ($permission !== null) {
            $this->setPermission($permission);
        }

        if (is_string($filler) && class_exists($filler)) {
            $this->setUrl($filler::url());
        } elseif ($filler instanceof MenuFiller) {
            $this->resolveMenuFiller($filler);
        } else {
            $this->setUrl($filler);
        }
    }

    /**
     * Подстановка url из ресурса
     */
    protected function resolveMenuFiller(MenuFiller $filler): void
    {
        $this->setUrl(fn (): string => '/'.$filler->url());
    }

    public function getFiller(): MenuFiller|Closure|string
    {
        return $this->filler;
    }

    public function badge(Closure $callback): static
    {
        $this->badge = $callback;

        return $this;
    }

    public function hasBadge(): bool
    {
        return is_callable($this->badge);
    }

    public function getBadge(): ?string
    {
        return call_user_func($this->badge);
    }

    public function setUrl(string|Closure|null $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function url(): string
    {
        return is_callable($this->url)
            ? call_user_func($this->url)
            : $this->url;
    }

    public function toArray(): array
    {
        return [
            'heading' => $this->getLabel(),
            'route' => $this->url(),
            'keenthemesIcon' => $this->getIcon(),
        ];
    }
}
