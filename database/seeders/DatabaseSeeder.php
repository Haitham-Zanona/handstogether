<?php

// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Database\Seeders\GroupSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            GroupSeeder::class,
            LectureSeeder::class,
            PaymentSeeder::class,
            AdmissionSeeder::class,
        ]);
    }
}
