<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::insert(
            '
                INSERT INTO `areas` (city_id, name, created_at)
                VALUES (1, "Al Awir", NOW()), (1, "Al Badaa", NOW()), (1, "Al Barari", NOW()), (1, "Al Barsha", NOW()), (1, "Al Barsha South", NOW()), (1, "Al Furjan", NOW()), (1, "Al Garhoud", NOW()), (1, "Al Hamriya", NOW()), (1, "Al Jaddaf", NOW()), (1, "Al Jafiliya", NOW()), (1, "Al Karama", NOW()), (1, "Al Khail Heights", NOW()), (1, "Al Khawaneej", NOW()), (1, "Al Mamzar", NOW()), (1, "Al Manara", NOW()), (1, "Al Mankhool", NOW()), (1, "Al Mizhar", NOW()), (1, "Al Nahda", NOW()), (1, "Al Quoz", NOW()), (1, "Al Qusais", NOW()), (1, "Al Rashidiya", NOW()), (1, "Al Safa", NOW()), (1, "Al Satwa", NOW()), (1, "Al Shindagha", NOW()), (1, "Al Sufouh", NOW()), (1, "Al Twar", NOW()), (1, "Al Warqa", NOW()), (1, "Al Wasl", NOW()), (1, "Arabian Ranches", NOW()), (1, "Arjan", NOW()), (1, "Barsha Heights (Tecom)", NOW()), (1, "Bur Dubai", NOW()), (1, "Business Bay", NOW()), (1, "Bluewaters Island/Street", NOW()), (1, "City Walk", NOW()), (1, "Culture Village", NOW()), (1, "Deira", NOW()), (1, "DIFC", NOW()), (1, "Discovery Gardens", NOW()), (1, "Downtown Dubai", NOW()), (1, "Dubai Festival City", NOW()), (1, "Dubai Healthcare City", NOW()), (1, "Dubai Hills Estate", NOW()), (1, "Dubai Investment Park", NOW()), (1, "Dubai Marina", NOW()), (1, "Dubai Media City", NOW()), (1, "Dubai Production City (IMPZ)", NOW()), (1, "Dubai Science Park", NOW()), (1, "Dubai Silicon Oasis", NOW()), (1, "Dubai South", NOW()), (1, "Dubai Sports City", NOW()), (1, "Dubai Studio City", NOW()), (1, "Dubai Sustainable City", NOW()), (1, "Dubai World Central (DWC)", NOW()), (1, "Dubailand", NOW()), (1, "Emirates Hills", NOW()), (1, "Falcon City of Wonders", NOW()), (1, "Garhoud", NOW()), (1, "Green Community", NOW()), (1, "International City", NOW()), (1, "Jaddaf Waterfront", NOW()), (1, "JBR (Jumeirah Beach Residence)", NOW()), (1, "Jebel Ali", NOW()), (1, "Jumeirah", NOW()), (1, "Jumeirah Golf Estates", NOW()), (1, "Jumeirah Islands", NOW()), (1, "Jumeirah Lake Towers (JLT)", NOW()), (1, "Jumeirah Park", NOW()), (1, "Jumeirah Village Circle (JVC)", NOW()), (1, "Jumeirah Village Triangle (JVT)", NOW()), (1, "Karama", NOW()), (1, "Knowledge Village", NOW()), (1, "Mankhool", NOW()), (1, "Meadows", NOW()), (1, "Meydan", NOW()), (1, "Mirdif", NOW()), (1, "Mohammad Bin Rashid City", NOW()), (1, "Motor City", NOW()), (1, "Muhaisnah", NOW()), (1, "Nad Al Hamar", NOW()), (1, "Nad Al Sheba", NOW()), (1, "Old Town", NOW()), (1, "Oud Metha", NOW()), (1, "Palm Jumeirah", NOW()), (1, "Port Saeed", NOW()), (1, "Ras Al Khor", NOW()), (1, "Remraam", NOW()), (1, "Satwa", NOW()), (1, "Sheikh Zayed Road", NOW()), (1, "Silicon Oasis", NOW()), (1, "The Greens", NOW()), (1, "The Lakes", NOW()), (1, "The Springs", NOW()), (1, "The Views", NOW()), (1, "Town Square", NOW()), (1, "Umm Al Sheif", NOW()), (1, "Umm Hurair", NOW()), (1, "Umm Ramool", NOW()), (1, "Umm Suqeim", NOW()), (1, "Wafi City", NOW()), (1, "Warsan", NOW()), (1, "World Trade Center Residences", NOW()), (1, "Za\'abeel", NOW());
            '
        );

        DB::insert(
            '
                INSERT INTO `areas` (city_id, name, created_at) VALUES (2, "Abu Dhabi City", NOW()), (2, "Al Aman", NOW()), (2, "Al Bahia", NOW()), (2, "Al Bateen", NOW()), (2, "Al Dhafra", NOW()), (2, "Al Falah", NOW()), (2, "Al Karamah", NOW()), (2, "Al Khaleej Al Arabi", NOW()), (2, "Al Khalidiya", NOW()), (2, "Al Madina Al Riyadiya", NOW()), (2, "Al Manaseer", NOW()), (2, "Al Manhal", NOW()), (2, "Al Maqtaa", NOW()), (2, "Al Marina", NOW()), (2, "Al Markaziyah", NOW()), (2, "Al Mina", NOW()), (2, "Al Muneera", NOW()), (2, "Al Mushrif", NOW()), (2, "Al Nahyan", NOW()), (2, "Al Qubesat", NOW()), (2, "Al Raha Beach", NOW()), (2, "Al Reem Island", NOW()), (2, "Al Rowdah", NOW()), (2, "Al Shamkha", NOW()), (2, "Al Wahda", NOW()), (2, "Baniyas", NOW()), (2, "Capital Centre", NOW()), (2, "Corniche", NOW()), (2, "Danet Abu Dhabi", NOW()), (2, "Defence Street", NOW()), (2, "Delma Street", NOW()), (2, "Eastern Road", NOW()), (2, "Electra Street", NOW()), (2, "Khalifa City", NOW()), (2, "Khalidiya Village", NOW()), (2, "Masdar City", NOW()), (2, "Mussafah", NOW()), (2, "Officers City", NOW()), (2, "Saadiyat Island", NOW()), (2, "Tourist Club Area (TCA)", NOW()), (2, "Yas Island", NOW());
            '
        );

        DB::insert(
            '
            INSERT INTO `areas` (city_id, name, created_at) VALUES (3, "Abu Shagara", NOW()), (3, "Al Azra", NOW()), (3, "Al Butina", NOW()), (3, "Al Fisht", NOW()), (3, "Al Ghafia", NOW()), (3, "Al Gharb", NOW()), (3, "Al Heera", NOW()), (3, "Al Jazzat", NOW()), (3, "Al Jubail", NOW()), (3, "Al Khan", NOW()), (3, "Al Majaz", NOW()), (3, "Al Manakh", NOW()), (3, "Al Mareija", NOW()), (3, "Al Mirgab", NOW()), (3, "Al Mujarrah", NOW()), (3, "Al Musalla", NOW()), (3, "Al Nabba", NOW()), (3, "Al Nahda", NOW()), (3, "Al Nekhailat", NOW()), (3, "Al Noaf", NOW()), (3, "Al Qadisiya", NOW()), (3, "Al Qasba", NOW()), (3, "Al Qasimia", NOW()), (3, "Al Qulayaa", NOW()), (3, "Al Ramaqia", NOW()), (3, "Al Ramla West", NOW()), (3, "Al Ramtha", NOW()), (3, "Al Riffa", NOW()), (3, "Al Rolla", NOW()), (3, "Al Sajaa", NOW()), (3, "Al Shahba", NOW()), (3, "Al Shuwaihean", NOW()), (3, "Al Soor", NOW()), (3, "Al Sweihat", NOW()), (3, "Al Taawun", NOW()), (3, "Al Tala’a", NOW()), (3, "Al Yarmook", NOW()), (3, "Bu Daniq", NOW()), (3, "Bu Tina", NOW()), (3, "Halwan", NOW()), (3, "Industrial Area", NOW()), (3, "Khuzam", NOW()), (3, "Maysaloon", NOW()), (3, "Muwaileh", NOW()), (3, "Nasma Residences", NOW()), (3, "Samnan", NOW()), (3, "Sharqan", NOW()), (3, "University City", NOW());
'
        );

        DB::insert(
            '
            INSERT INTO `areas` (city_id, name, created_at) VALUES (4, "Abu Hail", NOW()), (4, "Ain Al Fayda", NOW()), (4, "Al Agabiyya", NOW()), (4, "Al Ain Industrial Area", NOW()), (4, "Al Bateen", NOW()), (4, "Al Dhaher", NOW()), (4, "Al Falaj Hazzaa", NOW()), (4, "Al Foah", NOW()), (4, "Al Grayyeh", NOW()), (4, "Al Hayer", NOW()), (4, "Al Jahili", NOW()), (4, "Al Jimi", NOW()), (4, "Al Khabisi", NOW()), (4, "Al Khazna", NOW()), (4, "Al Maqam", NOW()), (4, "Al Masoudi", NOW()), (4, "Al Muaiji", NOW()), (4, "Al Mutaredh", NOW()), (4, "Al Muraba\'a", NOW()), (4, "Al Murabba", NOW()), (4, "Al Mwaiji", NOW()), (4, "Al Nahyan Camp", NOW()), (4, "Al Qattara", NOW()), (4, "Al Quaa", NOW()), (4, "Al Sinaiyah", NOW()), (4, "Al Tawia", NOW()), (4, "Al Yahar", NOW()), (4, "Hili", NOW()), (4, "Jahili", NOW()), (4, "Jimi", NOW()), (4, "Manaseer", NOW()), (4, "Mezyad", NOW()), (4, "Zakher", NOW());
        '
        );

        DB::insert(
            '
            INSERT INTO `areas` (city_id, name, created_at) VALUES (5, "Al Bustan", NOW()), (5, "Al Hamidiyah", NOW()), (5, "Al Helio", NOW()), (5, "Al Jarrf", NOW()), (5, "Al Jurf", NOW()), (5, "Al Mowaihat", NOW()), (5, "Al Naemiyah", NOW()), (5, "Al Nuaimiya", NOW()), (5, "Al Rashidiya", NOW()), (5, "Al Rumailah", NOW()), (5, "Al Sawan", NOW()), (5, "Al Zahra", NOW()), (5, "Corniche Ajman", NOW()), (5, "Emirates City", NOW()), (5, "Garden City", NOW()), (5, "Masfout", NOW()), (5, "Musherief", NOW()), (5, "Rawda", NOW()), (5, "Sheikh Khalifa Bin Zayed Street", NOW()), (5, "Uptown Ajman", NOW());
'
        );

        DB::insert(
            '  
            INSERT INTO `areas` (city_id, name, created_at) VALUES (6, "Adhan", NOW()), (6, "Al Dhait", NOW()), (6, "Al Hamra", NOW()), (6, "Al Hudaiba", NOW()), (6, "Al Jazeera", NOW()), (6, "Al Juwais", NOW()), (6, "Al Kharran", NOW()), (6, "Al Mairid", NOW()), (6, "Al Mamourah", NOW()), (6, "Al Mareed", NOW()), (6, "Al Nakheel", NOW()), (6, "Al Qusaidat", NOW()), (6, "Al Rams", NOW()), (6, "Al Seer", NOW()), (6, "Al Sharishah", NOW()), (6, "Al Turfa", NOW()), (6, "Al Uraibi", NOW()), (6, "Al Waridat", NOW()), (6, "Al Yarmook", NOW()), (6, "Corniche Al Qawasim", NOW()), (6, "Dafan Al Khor", NOW()), (6, "Dahan", NOW()), (6, "Diqdaqah", NOW()), (6, "Ghalilah", NOW()), (6, "Julfar", NOW()), (6, "Khor Khwair", NOW()), (6, "Khuzam", NOW()), (6, "Mina Al Arab", NOW()), (6, "Qubbah", NOW()), (6, "Rak City", NOW()), (6, "RAKIA", NOW()), (6, "Seih Al Burairat", NOW()), (6, "Shamal", NOW()), (6, "Sidroh", NOW()), (6, "Suhaim", NOW()), (6, "Wadi Al Beah", NOW());
        '
        );

        DB::insert(
            '
            INSERT INTO `areas` (city_id, name, created_at) VALUES (7, "Al Faseel", NOW()), (7, "Al Ghurfa", NOW()), (7, "Al Gurfa", NOW()), (7, "Al Hayl", NOW()), (7, "Al Hilia", NOW()), (7, "Al Madhab", NOW()), (7, "Al Majaz", NOW()), (7, "Al Manama", NOW()), (7, "Al Muroor", NOW()), (7, "Al Nakheel", NOW()), (7, "Al Qala\'a", NOW()), (7, "Al Qurayyah", NOW()), (7, "Al Ramlah", NOW()), (7, "Al Rashidiya", NOW()), (7, "Al Sharq", NOW()), (7, "Dibba Al Fujairah", NOW()), (7, "Dibba Al Hisn", NOW()), (7, "Khuzam", NOW()), (7, "Merashid", NOW()), (7, "Sakamkam", NOW()), (7, "Siji", NOW()), (7, "Tawyeen", NOW()), (7, "Teiba", NOW());
        '
        );

        DB::insert(
            '
            INSERT INTO `areas` (city_id, name, created_at) VALUES (8, "Al Abraq", NOW()), (8, "Al Dar Al Baida", NOW()), (8, "Al Haditha", NOW()), (8, "Al Hamra", NOW()), (8, "Al Raas", NOW()), (8, "Al Raudah", NOW()), (8, "Al Salama", NOW()), (8, "Al Soor", NOW()), (8, "Al Zahya", NOW()), (8, "Barracuda", NOW()), (8, "Emirates Modern Industrial Area", NOW()), (8, "Falaj Al Mualla", NOW()), (8, "Green Belt Park", NOW()), (8, "Industrial Area", NOW()), (8, "Mistral", NOW()), (8, "New Industrial Area", NOW()), (8, "Old Town", NOW()), (8, "Umm Al Quwain Marina", NOW()), (8, "Umm Al Quwain Motor City", NOW()), (8, "Umm Al Quwain Pearl", NOW());
        '
        );

        DB::update('UPDATE `cities` SET name="Ras Al Khaimah" WHERE id = 6');

        DB::delete('DELETE FROM `cities` WHERE id > 8');
    }

    public function down()
    {
    }
};
