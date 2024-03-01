<?php

use App\Models\NewsSubscription;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        seed_permissions(NewsSubscription::class, 'Newsletter Subscriptions');
    }

    public function down()
    {
    }
};
