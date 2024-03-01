<?php

namespace App\Console\Commands;

use App\Models\BackofficeUser;
use App\Models\Booking;
use App\Models\Lead\Lead;
use App\Models\Member\Member;
use App\Models\Member\MemberBillingDetail;
use App\Models\Member\MemberShippingDetail;
use App\Models\Member\Partner;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ObfuscateDataCommand extends Command
{
    protected $signature = 'obfuscate:data';

    protected $description = 'Command description';

    protected const DEFAULT_AVATAR = 'avatar.jpg';

    protected array $excludeIds = [
        1,
        2,
    ];

    protected array $excludeEmails = [
        'ivan@parasol.me',
        'rafik@myadv.me',
    ];

    public function handle(): void
    {
        $startTime = now();
        $this->info('Start time: '.$startTime->format('H:i:s'));

        $this->obfuscateMembers();
        $this->obfuscateLeads();
        $this->obfuscateBooking();

        $admin = BackofficeUser::find(1);
        $admin->password = bcrypt('11223344');
        $admin->save();

        $endTime = now();
        $this->info('End time: '.$endTime->format('H:i:s'));

        $duration = $endTime->diff($startTime);
        $this->info('Duration: '.$duration->format('%H:%I:%S'));
    }

    /**
     * Obfuscates booking data.
     */
    protected function obfuscateBooking(): void
    {
        $faker = Faker::create();

        Booking::withTrashed()->chunk(100, function ($bookings) use ($faker) {
            foreach ($bookings as $booking) {
                /** @var $booking Booking */

                if (!$booking->member_id) {
                    $booking->name = $faker->name;
                    $booking->email = $booking->id.'booking@advplus.ae';

                    if ($booking->phone) {
                        $booking->phone = $faker->e164PhoneNumber;
                    }
                }

                $booking->timestamps = false;

                $booking->withoutEvents(function () use ($booking) {
                    $booking->save();
                });

                $this->obfuscateBookingSnapshots($booking);
            }
        });
    }

    /**
     * Obfuscates booking snapshots by modifying snapshot data.
     *
     * This function processes and updates the booking snapshot data, ensuring that sensitive information is obfuscated or replaced with randomized data while retaining the structure of the data. It checks for specific keys in the data, such as 'billing', 'shipping', 'member', 'partner', and 'kids', and performs obfuscation as necessary for each data type. If corresponding records exist for members, billing details, shipping details, partners, or kids, the function uses their data to obfuscate the snapshot data. Otherwise, it generates random data to replace sensitive information.
     */
    protected function obfuscateBookingSnapshots(Booking $booking): void
    {
        $faker = Faker::create();

        if ($snapshot = $booking->snapshot) {
            $data = $snapshot->data;

            /**
             * @var $member Member
             */
            $member = $booking->member()->withTrashed()->first();

            if (key_exists('billing', $data)) {
                /**
                 * @var $billing MemberBillingDetail
                 */
                $billing = $member ? $member->memberBillingDetail()->withTrashed()->first() : false;

                $billingData = $data['billing'];

                $billingData['first_name'] = $this->getFirstName($booking, $member, $billing);
                $billingData['last_name'] = $this->getLastName($booking, $member, $billing);
                $billingData['street'] = $billing ? $billing->street : $faker->streetAddress;

                $data['billing'] = $billingData;
            }

            if (key_exists('shipping', $data)) {
                /**
                 * @var $shipping MemberShippingDetail
                 */
                $shipping = $member ? $member->memberShippingDetail()->withTrashed()->first() : false;

                $shippingData = $data['shipping'];

                $shippingData['first_name'] = $this->getFirstName($booking, $member, $shipping);
                $shippingData['last_name'] = $this->getLastName($booking, $member, $shipping);
                $shippingData['street'] = $shipping ? $shipping->street : $faker->streetAddress;

                $data['shipping'] = $shippingData;
            }

            if (key_exists('member', $data)) {
                $memberData = $data['member'];

                $memberData['first_name'] = $member->first_name;
                $memberData['last_name'] = $member->last_name;
                $memberData['phone'] = $member->phone;
                $memberData['email'] = $member->email;
                $memberData['business_email'] = $member->recovery_email;
                $memberData['main_email'] = $member->main_email;

                $data['member'] = $memberData;
            }

            if (key_exists('partner', $data)) {
                /**
                 * @var $partner Partner
                 */
                $partner = $member ? $member->partner()->withTrashed()->first() : false;

                $partnerData = $data['partner'];

                $partnerData['first_name'] = $partner ? $partner->first_name : $faker->firstName;
                $partnerData['last_name'] = $partner ? $partner->last_name : $faker->lastName;
                $partnerData['phone'] = $partner ? $partner->phone : $faker->e164PhoneNumber;
                $partnerData['email'] = $partner ? $partner->email : $faker->unique()->safeEmail;
                $partnerData['business_email'] = $partner ? $partner->recovery_email : $partnerData['email'];
                $partnerData['main_email'] = $partner ? $partner->main_email : array_key_first(Member::LOGIN);

                $data['partner'] = $partnerData;
            }

            if (key_exists('kids', $data) && count($data['kids'])) {
                $kids = $member->kids()->withTrashed()->get(['id', 'dob', 'first_name', 'last_name'])->map(
                    function ($kid) {
                        $kid['birthday'] = date('Y-m-d', strtotime($kid['dob']));
                        unset($kid['dob']);
                        return $kid;
                    }
                )->toArray();

                $data['kids'] = $kids;
            }

            if ($snapshot->data != $data) {
                $snapshot->data = $data;
                $snapshot->timestamps = false;
            }
        }
    }

    protected function getFirstName(Booking $booking, $member, $model): string
    {
        if ($model) {
            return $model->first_name;
        } elseif ($member) {
            return $member->first_name;
        }
        return current(explode(' ', $booking->name));
    }

    protected function getLastName(Booking $booking, $member, $model): string
    {
        if ($model) {
            return $model->last_name;
        } elseif ($member) {
            return $member->last_name;
        }
        $array = explode(' ', $booking->name);
        return end($array);
    }

    /**
     * Obfuscates member data by replacing or randomizing member information.
     *
     * This function processes and updates member data, ensuring that sensitive information is obfuscated or replaced with randomized data. It iterates through a chunk of members, excluding specific IDs and emails listed in the `excludeIds` and `excludeEmails` arrays. For each eligible member, it replaces the first name, last name, email, recovery email, and phone number with obfuscated or randomized data. The member's avatar is set to a default avatar, and timestamps are disabled during updates to avoid triggering events.
     * Additionally, this function calls other obfuscation functions to update related data such as bookings, member shipping details, and member billing details.
     */
    protected function obfuscateMembers(): void
    {
        $faker = Faker::create();

        Member::withTrashed()
            ->whereNotIn('id', $this->excludeIds)
            ->whereNotIn('email', $this->excludeEmails)
            ->chunk(100, function ($members) use ($faker) {
                foreach ($members as $member) {
                    /** @var $member Member */

                    $member->first_name = $faker->firstName;
                    $member->last_name = $faker->lastName;

                    $member->email = $member->id.'member@advplus.ae';
                    $member->login_email = $member->email;

                    if ($member->recovery_email) {
                        $member->recovery_email = $faker->unique()->safeEmail;
                    }

                    $member->avatar = static::DEFAULT_AVATAR;

                    if ($member->phone) {
                        $member->phone = $faker->e164PhoneNumber;
                    }

                    $member->timestamps = false;
                    $member->withoutEvents(function () use ($member) {
                        $member->save();
                    });

                    $this->obfuscateBookingByMember($member);
                    $this->obfuscateMemberShippingDetails($member);
                    $this->obfuscateMemberBillingDetails($member);
                }
            });
    }

    protected function obfuscateBookingByMember(Member $member): void
    {
        DB::table('bookings')
            ->where('member_id', $member->id)
            ->update([
                'email' => $member->email,
                'name' => $member->full_name,
                'phone' => $member->phone,
            ]);
    }

    protected function obfuscateMemberShippingDetails(Member $member): void
    {
        $faker = Faker::create();

        DB::table('member_shipping_details')
            ->where('member_id', $member->id)
            ->update([
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'street' => $faker->streetAddress,
            ]);
    }

    protected function obfuscateMemberBillingDetails(Member $member): void
    {
        $faker = Faker::create();

        DB::table('member_billing_details')
            ->where('member_id', $member->id)
            ->update([
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'street' => $faker->streetAddress,
            ]);
    }

    protected function obfuscateLeads(): void
    {
        $faker = Faker::create();

        Lead::withTrashed()->chunk(100, function ($leads) use ($faker) {
            foreach ($leads as $lead) {
                /** @var $lead Lead */

                if ($lead->first_name) {
                    $lead->first_name = $faker->firstName;
                }

                if ($lead->last_name) {
                    $lead->last_name = $faker->lastName;
                }

                if ($lead->email) {
                    $lead->email = $lead->id.'lead@advplus.ae';
                }

                if ($lead->phone) {
                    $lead->phone = $faker->e164PhoneNumber;
                }

                $lead->timestamps = false;

                $lead->withoutEvents(function () use ($lead) {
                    $lead->save();
                });
            }
        });
    }
}
