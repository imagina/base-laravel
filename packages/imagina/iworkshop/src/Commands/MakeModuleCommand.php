<?php

namespace Imagina\Iworkshop\Commands;

use Illuminate\Console\Command;
use Imagina\Iworkshop\Support\ScaffoldTrait;

class MakeModuleCommand extends Command
{
    use ScaffoldTrait;

    protected $signature = 'module:scaffold {moduleCreation?}';
    protected $description = 'Create a laravel-module | {module?}';

    public function handle(): void
    {
        $this->getModuleName($this->ARG_MODULE_CREATION);
        $this->createFolderStructure();
        $this->createInitialFilesFromStubs();
        $this->info("Package $this->moduleName created successfully at packages/imagina/$this->moduleName");
    }

    protected function createFolderStructure(): void
    {
        $folders = [
            'config',
            $this->appFolderPath . 'Models',
            $this->appFolderPath . 'Http/Controllers/Api',
            $this->appFolderPath . 'Transformers',
            'providers',
            $this->appFolderPath . 'Repositories/Eloquent',
            $this->appFolderPath . 'Repositories/Cache',
            'database/Factories',
            'database/Migrations',
            'database/Seeders',
            'routes',
            'test',
        ];
        foreach ($folders as $folder) mkdir("$this->modulePath/$folder", 0755, true);
    }

    protected function createInitialFilesFromStubs(): void
    {
        $this->generateFiles([
            ['stub' => '0-composer', 'destination' => 'composer.json'],
            ['stub' => '0-module', 'destination' => 'module.json'],
            ['stub' => '1-config', 'destination' => 'config/config.php'],
            ['stub' => '2-permissions', 'destination' => 'config/permissions.php'],
            ['stub' => '6-routes-web', 'destination' => 'routes/web.php'],
            ['stub' => '6-routes-api', 'destination' => 'routes/api.php'],
            ['stub' => '7-module-service-provider', 'destination' => 'providers/ModuleServiceProvider.php'],
        ]);
    }
}
