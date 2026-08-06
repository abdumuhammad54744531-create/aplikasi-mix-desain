<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator Laboratorium', 'email' => 'admin@mixdesign.local',
            'password' => 'Admin@12345', 'role' => 'administrator', 'must_change_password' => true, 'is_active' => true,
        ]);
        $this->call(StandardReferenceSeeder::class);
        $this->call(JmdStandardMasterSeeder::class);
    }
}
