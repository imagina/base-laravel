<?php

namespace Modules\Iuser\Services;

use Illuminate\Support\Facades\Hash;

use Modules\Iuser\Repositories\UserRepository;

class UserService
{

    private $userRepository;

    /**
     * Construct
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new user with the provided data.
     * @param $checkUserExists | Used By UserApiController and CreateUsersSeeder
     */
    public function createUser($data,$checkUserExists = true)
    {

        //Check if user already exists
        if ($checkUserExists) {
            $user = $this->userRepository->getItem($data['email'], json_decode(json_encode(["filter" => ["field" => "email"]])));
            if($user)
                return $user;
        }

        //No Existing User, Create a new one
        $dataToCreate = [
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'email' => strtolower($data['email']),
            'password' =>  $data['password'], //Hash::make($data['password'])
            'roles' => isset($data['roles']) ? $data['roles'] : [2], //Default role is 2 (user)
        ];

        $user = $this->userRepository->create($dataToCreate);



        return $user;


    }




}
