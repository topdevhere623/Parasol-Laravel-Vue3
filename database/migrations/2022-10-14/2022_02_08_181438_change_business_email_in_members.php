<?php

use App\Traits\EnumChangeTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBusinessEmailInMembers extends Migration
{
    use EnumChangeTrait;

    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->renameColumn('business_email', 'recovery_email');
        });

        $this->setEnumValues(
            'members',
            'main_email',
            [
                'personal_email',
                'business_email',
                'recovery_email',
            ],
            false,
            'personal_email'
        );

        \App\Models\Member\Member::whereMainEmail('business_email')->update([
            'main_email' => 'recovery_email',
        ]);
        $this->setEnumValues(
            'members',
            'main_email',
            [
                'personal_email',
                'recovery_email',
            ],
            false,
            'personal_email'
        );
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->renameColumn('recovery_email', 'business_email');
        });

        $this->setEnumValues(
            'members',
            'main_email',
            [
                'personal_email',
                'business_email',
                'recovery_email',
            ],
            false,
            'personal_email'
        );

        \App\Models\Member\Member::whereMainEmail('recovery_email')->update([
            'main_email' => 'business_email',
        ]);

        $this->setEnumValues(
            'members',
            'main_email',
            [
                'personal_email',
                'business_email',
            ],
            false,
            'personal_email'
        );
    }
}
