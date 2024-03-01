<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToPrograms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->after('color', function (Blueprint $table) {
                $table->string('passkit_faq_url_ios');
                $table->string('passkit_faq_url_android');
                $table->boolean('passkit_button_on_top')->default(0);

                $table->boolean('has_access_clubs')->default(1);
                $table->boolean('has_access_about_membership')->default(1);
                $table->boolean('has_access_profile')->default(1);
                $table->boolean('has_access_referrals')->default(1);
                $table->boolean('has_access_offers')->default(1);
                $table->boolean('has_access_visiting_family_membership')->default(1);
                $table->boolean('has_access_password_change')->default(1);
                $table->boolean('has_access_logout')->default(1);
                $table->boolean('has_access_contact_us')->default(1);

                $table->string('terms_and_conditions_url');
                $table->string('faq_page_url');
                $table->string('club_guide_url');
                $table->string('whatsapp_url');

                $table->text('contact_us_page');
            });

            $table->renameColumn('member_portal_header_color', 'member_portal_main_color');
        });

        \DB::table('programs')->update([
            'passkit_faq_url_ios' => 'https://advplus.ae/uploads/documents/digital-membership-card-guide-ios.pdf',
            'passkit_faq_url_android' => 'https://advplus.ae/uploads/documents/digital-membership-card-guide-android.pdf',
            'has_access_contact_us' => 0,
            'whatsapp_url' => 'https://wa.link/hds8te',

        ]);

        \DB::table('programs')->where('source', \App\Models\Program::SOURCE_MAP['hsbc'])->update([
            'has_access_about_membership' => 0,
            'has_access_referrals' => 0,
            'has_access_visiting_family_membership' => 0,
            'passkit_faq_url_ios' => 'https://advplus.ae/uploads/documents/hsbcsoleil-guide-ios.pdf',
            'passkit_faq_url_android' => 'https://advplus.ae/uploads/documents/hsbcsoleil-guide-android.pdf',
            'passkit_button_on_top' => 1,
        ]);

        \DB::table('programs')->where('source', \App\Models\Program::SOURCE_MAP['gems'])->update([
            'has_access_about_membership' => 0,
            'has_access_referrals' => 0,
            'has_access_password_change' => 0,
            'has_access_logout' => 0,
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
            $table->dropColumn('passkit_faq_url_ios');
            $table->dropColumn('passkit_faq_url_android');
            $table->dropColumn('passkit_button_on_top');

            $table->dropColumn('has_access_clubs');
            $table->dropColumn('has_access_about_membership');
            $table->dropColumn('has_access_profile');
            $table->dropColumn('has_access_referrals');
            $table->dropColumn('has_access_offers');
            $table->dropColumn('has_access_visiting_family_membership');
            $table->dropColumn('has_access_password_change');
            $table->dropColumn('has_access_logout');
            $table->dropColumn('has_access_contact_us');

            $table->dropColumn('terms_and_conditions_url');
            $table->dropColumn('faq_page_url');
            $table->dropColumn('club_guide_url');
            $table->dropColumn('whatsapp_url');

            $table->dropColumn('contact_us');

            $table->renameColumn('member_portal_main_color', 'member_portal_header_color');
        });
    }
}
