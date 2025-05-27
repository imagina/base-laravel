<?php

namespace Modules\Iuser\Database\Seeders;

use Illuminate\Database\Seeder;

use Illuminate\Database\Eloquent\Model;

class CreateUsersSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Model::unguard();


        \Log::info('Este es un mensaje de registro informativo.');

        $this->command->info('pruebaaaa');

    }

}
