<?php

namespace Modules\Iuser\Support\Permissions;

//use Nwidart\Modules\Contracts\RepositoryInterface;

class PermissionManager
{
    /**
     * @var RepositoryInterface
     */
    private $module;

    public function __construct()
    {
        $this->module = app('modules');
    }

    /**
     * Get the permissions from all the enabled modules
     */
    public function all(): array
    {
        $permissions = [];
        foreach ($this->module->allEnabled() as $enabledModule) {
            $configuration = config(strtolower('asgard.'.$enabledModule->getName()).'.permissions');
            if ($configuration) {
                $permissions[$enabledModule->getName()] = $configuration;
            }
        }

        return $permissions;
    }




}
