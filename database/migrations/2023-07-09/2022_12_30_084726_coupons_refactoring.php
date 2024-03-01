<?php

use App\Models\Channel;
use App\Models\Coupon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        // add value 'referrals_inactive' to coupons.status
        DB::statement(
            "ALTER TABLE coupons CHANGE COLUMN status status ENUM('referrals_inactive','inactive','active','expired','redeemed','member_unknown') NOT NULL DEFAULT 'inactive'"
        );
        Schema::table('coupons', function (Blueprint $table) {
            $table
                ->foreignId('channel_id')
                ->after('id')
                ->nullable()
                ->index();
            $table->renameColumn('type', 'amount_type');
            $table->char('code', 20)->change();
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table
                ->enum('type', [
                    Coupon::TYPES['bulk'],
                    Coupon::TYPES['individually'],
                    Coupon::TYPES['referral'],
                ])
                ->default('individually')
                ->after('code');
        });
        // transferring data from member_intro member_id and owner
        // transfer data about the channel and member
        DB::table('coupons')
            ->where('owner', Channel::MEMBER_REFERRAL_NAME)
            ->orWhere('owner', 'Member')
            ->update([
                'channel_id' => Channel::MEMBER_REFERRAL_ID,
                'owner' => '',
            ]);
        DB::table('coupons')
            ->where('owner', 'Member Referral - GEMS HR')
            ->update([
                'channel_id' => 2,
                'owner' => '',
            ]);
        $coupons = DB::table('coupons')
            ->whereRaw('member_id IS NULL')
            ->select(['owner'])
            ->groupBy('owner')
            ->get();
        // save channels
        foreach ($coupons as $coupon) {
            if (!$coupon->owner) {
                continue;
            }
            DB::insert(
                "
                INSERT INTO channels (title)
                VALUE ('{$coupon->owner}')
            "
            );
            DB::table('coupons')
                ->where('owner', $coupon->owner)
                ->update([
                    'channel_id' => DB::getPdo()->lastInsertId(),
                ]);
        }
        // add morph fields
        Schema::table('coupons', function (Blueprint $table) {
            $table
                ->string('couponable_type')
                ->index()
                ->after('id');
            $table
                ->integer('couponable_id')
                ->index()
                ->nullable()
                ->default(null)
                ->after('id');
        });
        // // M A P   M E M B E R S
        // if owner = member then type is referral
        DB::table('coupons')
            ->whereRaw('member_id IS NOT NULL')
            ->update(['type' => Coupon::TYPES['referral']]);
        $coupons = DB::table('coupons')
            ->whereRaw('member_id IS NOT NULL')
            ->select(['id', 'member_id'])
            ->get()
            ->toArray();
        $couponIds = $whenThen = [];
        foreach ($coupons as $coupon) {
            $whenThen[] = "WHEN `id` = {$coupon->id} THEN {$coupon->member_id}";
            $couponIds[] = $coupon->id;
        }
        $whenThen = implode("\n", $whenThen);
        if (!empty($whenThen)) {
            $couponIds = implode(',', $couponIds);
            DB::statement(
                "
                UPDATE `coupons` SET
                    `couponable_type` = 'App\\\Models\\\Member\\\Member',
                    `couponable_id` = CASE {$whenThen}
                END
                WHERE id IN ({$couponIds})
            "
            );
        }
        // MAP SALESPEOPLE
        // if code starts with "SG-" or corporate name = "RMS solutions" then type is bulk
        DB::table('coupons')
            ->where('code', 'LIKE', 'SG-%')
            ->orWhere('corporate_name', 'RMS solutions')
            ->update(['type' => Coupon::TYPES['bulk']]);
        $backofficeUsers = DB::table('backoffice_users')
            ->select(['id', 'first_name'])
            ->get();
        foreach ($backofficeUsers as $backofficeUser) {
            if (
                !$couponIds = DB::table('coupons')
                    ->where('owner', $backofficeUser->first_name)
                    ->pluck('id')
                    ->toArray()
            ) {
                continue;
            }
            $whenThen = [];
            foreach ($couponIds as $couponId) {
                $whenThen[] = "WHEN `id` = {$couponId} THEN {$backofficeUser->id}";
            }
            $whenThen = implode("\n", $whenThen);
            $couponIds = implode(',', $couponIds);
            DB::statement(
                "
                UPDATE `coupons` SET
                    `couponable_type` = 'App\\\Models\\\BackofficeUser',
                    `couponable_id` = CASE {$whenThen}
                END
                WHERE id IN ({$couponIds})
            "
            );
        }
        // remove odd columns
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign('coupons_member_id_foreign');
            $table->dropColumn('owner');
            $table->dropColumn('member_intro');
            $table->dropColumn('member_id');
        });
    }

    public function down()
    {
        //
    }
};
