<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferralsPageColumnToPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->text('referrals_page')->nullable()->after('contact_us_page');
        });

        \App\Models\Program::where('has_access_referrals', 1)->update([
            'referrals_page' => '<p>Refer A Friend</p>
          <p>SHARE THE LIFESTYLE, DOUBLE THE FUN!</p>
          <p>
            People you refer can avail membership discount, and you have these options to choose from as a reward once
            they have joined:
          </p>
          <ul>
            <li><p>Extra free month (for each membership) or</p></li>
            <li><p>Cashback of AED 150 (for each membership) or</p></li>
            <li>Add an additional club (for three memberships that joined)</li>
          </ul>
          <p>*Can not be used in conjunction with any other offer</p>',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('referrals_page');
        });
    }
}
