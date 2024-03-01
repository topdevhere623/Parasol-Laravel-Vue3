<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        if (!Schema::hasTable('zoho_oauth')) {
            Schema::create('zoho_oauth', function (Blueprint $table) {
                $table->increments('id');
                $table->string('access_token');
                $table->string('refresh_token');
                $table->bigInteger('expires');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('zoho_customers')) {
            Schema::create('zoho_customers', function (Blueprint $table) {
                $table->string('contact_id')->comment('Contact and customer are the same namings.');
                $table->string('contact_name');
                $table->unsignedBigInteger('member_id')->nullable();
                $table->string('email');

                $table->unique('contact_id');
            });
        }

        if (!Schema::hasColumns('payments', ['zoho_invoice_id'])) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('zoho_invoice_id')->after('invoice_number')->nullable();
            });
        }

        Setting::updateOrCreate(
            ['key' => 'zoho_organization_id'],
            ['value' => '826103220',
                'editable' => 1,
                'value_type' => 'string',],
        );
        Setting::updateOrCreate(
            ['key' => 'zoho_account_deposit_id'],
            ['value' => '4396688000000078206',
                'editable' => 1,
                'value_type' => 'string',],
        );
        Setting::updateOrCreate(
            ['key' => 'zoho_template_id'],
            ['value' => '4472868000000017001',
                'editable' => 1,
                'value_type' => 'string',],
        );
        Setting::updateOrCreate(
            ['key' => 'zoho_tax_id'],
            ['value' => '4472868000000078411',
                'editable' => 1,
                'value_type' => 'string',],
        );
        Setting::updateOrCreate(
            ['key' => 'zoho_membership_item_id'],
            ['value' => '4472868000000078390',
                'editable' => 1,
                'value_type' => 'string',],
        );
        Setting::updateOrCreate(
            ['key' => 'zoho_currency_id'],
            ['value' => '4472868000000078118',
                'editable' => 1,
                'value_type' => 'string',],
        );

        if (!Schema::hasTable('zoho_invoices')) {
            Schema::create('zoho_invoices', function (Blueprint $table) {
                $table->string('id');
                $table->string('customer_id');
                $table->string('invoice_number')->nullable();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->string('status')->nullable();
                $table->date('date')->nullable();
                $table->dateTime('created_time')->nullable();
                $table->double('total')->nullable();
                $table->double('discount')->nullable();
                $table->double('tax_total')->nullable();
                $table->text('invoice_url')->nullable();
                $table->text('full_response');

                $table->primary('id');
            });
        }

        if (!Schema::hasColumn('payment_methods', 'zoho_chartofaccount_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->string('zoho_chartofaccount_id')->after('uuid')->nullable();
            });
        }

        if (!Schema::hasTable('zoho_chartofaccounts')) {
            Schema::create('zoho_chartofaccounts', function (Blueprint $table) {
                $table->string('id');
                $table->string('account_name')->nullable();
                $table->string('account_code')->nullable();
                $table->string('account_type')->nullable();
                $table->boolean('is_user_created')->nullable();
                $table->boolean('is_system_account')->nullable();
                $table->boolean('is_standalone_account')->nullable();
                $table->boolean('is_active')->nullable();
                $table->boolean('can_show_in_ze')->nullable();
                $table->boolean('is_involved_in_transaction')->nullable();
                $table->string('current_balance')->nullable();
                $table->string('parent_account_id')->nullable();
                $table->string('parent_account_name')->nullable();
                $table->string('depth')->nullable();
                $table->boolean('has_attachment')->nullable();
                $table->string('is_child_present')->nullable();
                $table->string('child_count')->nullable();
                $table->dateTime('created_time')->nullable();
                $table->dateTime('last_modified_time')->nullable();

                $table->primary('id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('zoho_oauth');
        Schema::dropIfExists('zoho_customers');
        if (Schema::hasColumns('payments', ['zoho_invoice_id'])) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('zoho_invoice_id');
            });
        }

        Setting::query()
            ->whereIn(
                'key',
                [
                    'zoho_organization_id',
                    'zoho_account_deposit_id',
                    'zoho_template_id',
                    'zoho_tax_id',
                    'zoho_membership_item_id',
                    'zoho_currency_id',
                ]
            )
            ->delete();
        Schema::dropIfExists('zoho_invoices');

        if (Schema::hasColumn('payment_methods', 'zoho_chartofaccount_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropColumn('zoho_chartofaccount_id');
            });
        }

        Schema::dropIfExists('zoho_chartofaccounts');
    }
};
