<?php

namespace Modules\Iuser\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Hash;

use Modules\Iuser\Repositories\UserRepository;

class CreateUsersSeeder extends Seeder
{

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        $this->createSuperAdminUser();
    }


    private function createSuperAdminUser(): void
    {

        //TODO - Ver si se cambia de aqui, pasar al .env
        $password = 'test';

        //Data Base
        $data = [
            'email' => 'soporte@imaginacolombia.com',
            'password' => Hash::make($password),
            'first_name' => 'Imagina',
            'last_name' => 'Colombia'
        ];

        //TODO
        //Esto tocara extraerlo porque posiblemente Register o Create lo necesite
        //Tambien sera mejor enviar el roleId como parametro

        //Only the first time
        $user = $this->userRepository->getItem(['email' => $data['email']]);
        if (!$user) {
            $user = $this->userRepository->create($data);
            $user->roles()->attach(1); // Assuming 1 is the ID for the Super Admin role
        }

    }

}
