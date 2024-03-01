<?php

use App\Models\Partner\PartnerContact;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        seed_permissions(PartnerContact::class, 'Partner Contacts');
    }

    public function down()
    {
    }
};
