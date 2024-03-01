<?php

namespace App\Http\Controllers\Api\Crm;

use Illuminate\Routing\Controller as BaseController;
use ParasolCRM\CRUD;

class CrmBaseController extends BaseController
{
    use CRUD;

    // TODO: Remove
    protected function isAdmin(): bool
    {
        if ($user = \Auth::guard('backoffice_user')->user()) {
            return $user->hasRole('supervisor');
        }
        // TODO: По непонятным причинам иногда $user = null
        return true;
    }
}
