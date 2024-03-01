<?php

namespace Database\Seeders;

use App\Models\HSBCBin;
use App\Models\Payments\PaymentMethod;
use Illuminate\Database\Seeder;
use ParasolCRM\Activities\Facades\Activity;

class HSBCSeeder extends Seeder
{
    public function run()
    {
        Activity::disable();

        $bins = [
            [
                'type' => 'test',
                'title' => 'Test',
                'bin' => '424242',
                'free_checkout' => true,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Zero',
                'bin' => '491237',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Pl. Select',
                'bin' => '411939',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Cashback ',
                'bin' => '418348',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Black',
                'bin' => '539830',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Advance',
                'bin' => '517722',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Premier',
                'bin' => '552102',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Gold',
                'bin' => '491236',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'HSBC Platinum',
                'bin' => '402558',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'New Cashback card (to be launched)',
                'bin' => '407075',
                'free_checkout' => false,
            ],
            [
                'type' => 'credit',
                'title' => 'New REWARDS/SOLEIL card',
                'bin' => '407095',
                'free_checkout' => true,
            ],
            [
                'type' => 'debit',
                'title' => 'HSBC PB',
                'bin' => '419647',
                'free_checkout' => false,
            ],
            [
                'type' => 'debit',
                'title' => 'HSBC Advance',
                'bin' => '419648',
                'free_checkout' => false,
            ],
            [
                'type' => 'debit',
                'title' => 'HSBC Premier',
                'bin' => '419649',
                'free_checkout' => false,
            ],
            [
                'type' => 'debit',
                'title' => 'HSBC Jade',
                'bin' => '428689',
                'free_checkout' => false,
            ],
            [
                'type' => 'debit',
                'title' => 'HSBC Platinum',
                'bin' => '428688',
                'free_checkout' => false,
            ],
        ];

        foreach ($bins as $bin) {
            $model = new HSBCBin();
            $model->type = $bin['type'];
            $model->title = $bin['title'];
            $model->bin = $bin['bin'];
            $model->free_checkout = $bin['free_checkout'];
            $model->save();
        }

        $paymentMethod = new PaymentMethod();
        $paymentMethod->title = 'HSBC Checkout';
        $paymentMethod->code = PaymentMethod::HSBC_CHECKOUT_CODE;
        $paymentMethod->save();
    }
}
