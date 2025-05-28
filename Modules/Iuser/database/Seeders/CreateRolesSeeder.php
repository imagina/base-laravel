<?php

namespace Modules\Iuser\Database\Seeders;

use Illuminate\Database\Seeder;

use Illuminate\Console\Scheduling\Schedule;
//use Modules\User\Permissions\PermissionManager;
use Illuminate\Database\Eloquent\Model;
use Modules\Iuser\Models\Role;
use Modules\Iuser\Repositories\RoleRepository;

class CreateRolesSeeder extends Seeder
{

    private $schedule;
    private $roleRepository;


    public function __construct(Schedule $schedule, RoleRepository $roleRepository)
    {

        $this->schedule = $schedule;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();
        $this->schedule->command('php artisan config:clear');


        $this->createSuperAdminRol();

        //Crear User Role
        //Crear Admin Role

    }

    private function createSuperAdminRol():void
    {
        $roleData = [
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'en' => ['title' => trans("iuser::roles.types.super admin",[],"en")],
            'es' => ['title' => trans("iuser::roles.types.super admin",[],"es")]
        ];

        $role = $this->roleRepository->updateOrCreate(['slug'=>'super-admin'],$roleData);

        //metodo viejo
        //$roleSAdmin = createOrUpdateRole($roleData);
    }



}
