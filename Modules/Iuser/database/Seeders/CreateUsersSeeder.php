<?php

namespace Modules\Iuser\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
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
        //TODO - Check this data to change it
        $email = 'soporte@imaginacolombia.com';
        $password = 'baseImagina123';

        $params = json_decode(json_encode(["filter" => ["field" => "email"]]));
        $user = $this->userRepository->getItem($email, $params);

        if(empty($user)) {

            //Data Base
            $data = [
                'email' => $email,
                'password' => $password,
                'first_name' => 'Imagina',
                'last_name' => 'Colombia'
            ];

            $user = $this->userRepository->create($data);

            //TO CHECK: //In seeder , repo validations "beforeCreate" are not applied :/
            if($user)
                $user->roles()->attach(1);//Sync with Super Admin role
        }

    }

}
