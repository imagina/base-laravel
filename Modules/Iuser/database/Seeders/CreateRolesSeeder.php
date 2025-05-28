<?php

namespace Modules\Iuser\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;

use Modules\Iuser\Repositories\RoleRepository;
use Modules\Iuser\Support\Permissions\PermissionManager;

class CreateRolesSeeder extends Seeder
{

    private $schedule;
    private $roleRepository;
    private $permissions;

    public function __construct(
        Schedule $schedule,
        RoleRepository $roleRepository,
        PermissionManager $permissions
    ){
        $this->schedule = $schedule;
        $this->roleRepository = $roleRepository;
        $this->permissions = $permissions;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        //Version anterior
        $this->schedule->command('php artisan config:clear');

        $this->createSuperAdminRol();
        $this->createUserRol();
        $this->createAdminRol();

    }

    /**
     * TODO: Permisos, probar si no se asignan permisos y se utiliza lo del gate
     */
    private function createSuperAdminRol():void
    {
        $roleData = [
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'en' => ['title' => trans("iuser::roles.types.super admin",[],"en")],
            'es' => ['title' => trans("iuser::roles.types.super admin",[],"es")]
        ];

        $role = $this->roleRepository->updateOrCreate(['slug'=>'super-admin'],$roleData);
    }

    /**
     * TODO: Este creo que no lleva permisos por defecto
     */
    private function createUserRol():void
    {
        $roleData = [
            'name' => 'User',
            'slug' => 'user',
            'en' => ['title' => trans("iuser::roles.types.user",[],"en")],
            'es' => ['title' => trans("iuser::roles.types.user",[],"es")]
        ];

        $role = $this->roleRepository->updateOrCreate(['slug'=>'user'],$roleData);
    }

    /**
     * Create the Admin role with all permissions.
     */
    private function createAdminRol():void
    {

        $roleData = [
            'name' => 'Admin',
            'slug' => 'admin',
            'en' => ['title' => trans("iuser::roles.types.admin",[],"en")],
            'es' => ['title' => trans("iuser::roles.types.admin",[],"es")]
        ];

        $role = $this->roleRepository->updateOrCreate(['slug'=>'admin'],$roleData);

        //Set all permissions
        $this->setAllPermissions($role);

    }

    /**
     * Set all permissions for the given role.
     *
     */
    private function setAllPermissions($role): void
    {

        $permissions = $this->permissions->all();

        $modules = array_keys(app('modules')->allEnabled());
        $allPermissions = [];

        //Get permissions and set true
        foreach ($permissions as $moduleName => $modulePermissions) {
            if (in_array($moduleName, $modules)) {
                foreach ($modulePermissions as $entityName => $entityPermissions) {
                    foreach ($entityPermissions as $permissionName => $permission) {
                        $allPermissions["{$entityName}.{$permissionName}"] = true;
                    }
                }
            }
        }

        //Set all permissions to the role
        $role->permissions = $allPermissions;
        $role->save();

    }


}
