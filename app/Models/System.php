<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class System extends Model
{
    use Notifiable;

    public const DEFAULT_SYSTEM_ID = 1;

    protected $table = 'systems';

    public $timestamps = false;
}
