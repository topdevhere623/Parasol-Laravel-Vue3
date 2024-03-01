<?php

namespace App\ParasolCRMV2\Resources;

use App\Models\Club\BackofficeUserClubAdmin;

class ClubAdminResource extends BackofficeUserResource
{
    public static $model = BackofficeUserClubAdmin::class;

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
        return 'Club Administrator';
    }

    public static function label(): string
    {
        return 'Club Administrators';
    }
}
