<?php

namespace App\ParasolCRM\Resources;

use App\Models\BackofficeUserProgramAdmin;

class ProgramAdminResource extends BackofficeUserResource
{
    public static $model = BackofficeUserProgramAdmin::class;

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    public const STATUS_BADGES = [
        'active' => 'green',
        'inactive' => 'red',
    ];

    public static function singularLabel(): string
    {
        return 'Program Administrator';
    }

    public static function label(): string
    {
        return 'Program Administrators';
    }
}
