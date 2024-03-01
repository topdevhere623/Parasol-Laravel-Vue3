<?php

declare(strict_types=1);

namespace ParasolCRMV2\Menu;

use Illuminate\Support\Collection;

class MenuManager
{
    protected static ?Collection $menu = null;
    protected static ?Collection $rawMenu = null;

    /**
     * @param array<MenuElement> $rawMenuTree
     *
     * @return void
     */
    public static function register(array $rawMenuTree): void
    {
        self::$rawMenu = collect($rawMenuTree);
    }

    public static function all(): ?Collection
    {
        return self::$rawMenu?->filter(function (MenuElement $item) {
            if ($item->isGroup()) {
                $item->setItems(
                    $item->items()->filter(fn (MenuElement $child) => self::userCanAccessMenu($child))
                );

                return $item->items()->isNotEmpty() && self::userCanAccessMenu($item);
            }

            return self::userCanAccessMenu($item);
        });
    }

    private static function userCanAccessMenu(MenuElement $menu): bool
    {
        if ($menu->getHasAccess() === null) {
            return auth()->user()->hasPermission($menu->getPermission());
        }

        return $menu->getHasAccess();
    }
}
