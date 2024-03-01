<?php

namespace App\Models\Member;

use App\Models\BaseModel;
use App\Models\Traits\HasMemberRelation;
use App\Models\Traits\Selectable;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipType extends BaseModel
{
    use SoftDeletes;
    use HasMemberRelation;
    use Selectable;

    protected $selectableValue = 'title';
}
