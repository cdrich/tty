<?php

namespace Database\Seeders;
use App\Models\User; 
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        //  $user = User::factory()->create();
         User::factory()->count(50)->create();
        // factory(App\Models\User::class, 50)->create(); // Crée 50 utilisateurs 
    }
}
