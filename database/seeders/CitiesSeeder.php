<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    private const UAE_ID = 237;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = [
            'Dubai',
            'Abu Dhabi',
            'Sharjah',
            'Al Ain',
            'Ajman',
            'RAK City',
            'Fujairah',
            'Umm al-Quwain',
            'Dibba Al-Fujairah',
            'Khor Fakkan',
            'Kalba',
            'Jebel Ali',
            'Madinat Zayed',
            'Ruwais',
            'Liwa Oasis',
            'Dhaid',
            'Ghayathi',
            'Ar-Rams',
            'Dibba Al-Hisn',
            'Hatta',
            'Al Madam',
        ];

        foreach ($cities as $city) {
            $model = new City();
            $model->country_id = self::UAE_ID;
            $model->name = $city;
            $model->save();
        }
    }
}
