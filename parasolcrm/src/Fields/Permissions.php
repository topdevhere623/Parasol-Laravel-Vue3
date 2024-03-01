<?php

namespace ParasolCRM\Fields;

use App\Models\Laratrust\Permission;
use Illuminate\Support\Collection;

class Permissions extends BelongsToMany
{
    public string $component = 'PermissionsField';

    public function __construct($relationName, $relatedClass, $name, $label = null, $attrs = null)
    {
        parent::__construct($relationName, $relatedClass, $name, $label, $attrs);

        $this->options($this->relatedClass::oldest('display_name')->pluck('name', 'id')->toArray());
    }

    public function options(array|Collection $options): self
    {
        $groupedOptions = [];

        $permissions = Permission::oldest('display_name')->get();

        foreach ($options as $option) {
            if ($option && str_contains($option, '-')) {
                [$permission, $name] = explode('-', $option);
                $currentPermission = $permissions->where('name', $option)->first();

                $groupedOptions[$name]['module'] = $currentPermission->display_name ?? $name;

                if (in_array($permission, ['index', 'view', 'create', 'update', 'delete', 'log', 'export'])) {
                    $groupedOptions[$name][$permission]['value'] = false;
                    $groupedOptions[$name][$permission]['id'] = $currentPermission->id;
                }
            }
        }
        $this->withMeta(['options' => array_values($groupedOptions)]);

        return $this;
    }
}
