<?php

namespace Modules\Iuser\Database\Seeders;

use Illuminate\Database\Seeder;

class IuserDatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $this->call(CreateRolesSeeder::class);
        $this->call(CreateUsersSeeder::class);

    }

}
