<?php

namespace App\Models\Club;

use App\Models\BaseModel;

class ProgramClubSort extends BaseModel
{
    protected $table = 'programs_to_clubs_sort';
    protected $primaryKey = ['program_id', 'club_id'];
    protected $fillable = ['sort', 'program_id', 'club_id'];

    public $timestamps = false;
    public $incrementing = false;
}
