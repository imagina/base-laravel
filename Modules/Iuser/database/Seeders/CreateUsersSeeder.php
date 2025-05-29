<?php

namespace Modules\Iuser\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Modules\Iuser\Services\UserService;

class CreateUsersSeeder extends Seeder
{

    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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

        //Data Base
        $data = [
            'email' => 'soporte@imaginacolombia.com',
            'password' => 'baseImagina123', //TODO - Cambiar ubicacion
            'first_name' => 'Imagina',
            'last_name' => 'Colombia',
            'roles' => [1], //Super Admin role
        ];

        $user = $this->userService->createUser($data);

    }

}
