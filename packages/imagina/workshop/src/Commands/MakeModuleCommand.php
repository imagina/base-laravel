<?php

namespace Imagina\Workshop\Commands;

use Illuminate\Console\Command;
use Imagina\Workshop\Support\ScaffoldTrait;

class MakeModuleCommand extends Command
{
    use ScaffoldTrait;

    protected $signature = 'module:scaffold {module?}';
    protected $description = 'Create a laravel-module';

    public function handle(): void
    {
        $this->getModuleName('creating');
        $this->createFolderStructure();
        $this->createInitialFilesFromStubs();
        $this->info("Package $this->moduleName created successfully at packages/imagina/$this->moduleName");
    }

    protected function createFolderStructure(): void
    {
        $folders = [
            'config',
            'app/Models',
            'app/Http/Controllers',
            'app/Http/Transformers',
            'providers',
            'app/Repositories/Eloquent',
            'app/Repositories/Cache',
            'Database/Factories',
            'Database/Migrations',
            'Database/Seeders',
            'routes',
            'test',
        ];

        foreach ($folders as $folder) mkdir("$this->modulePath/$folder", 0755, true);
    }

    protected function createInitialFilesFromStubs(): void
    {
        $files = [
            ['stub' => '0-composer', 'destination' => 'composer.json'],
            ['stub' => '0-module', 'destination' => 'module.json'],
            ['stub' => '1-config', 'destination' => 'config/config.php'],
            ['stub' => '2-permissions', 'destination' => 'config/permissions.php'],
            ['stub' => '6-routes-web', 'destination' => 'routes/web.php'],
            ['stub' => '6-routes-api', 'destination' => 'routes/api.php'],
            ['stub' => '7-module-service-provider', 'destination' => 'providers/ModuleServiceProvider.php'],
        ];

        foreach ($files as $file) {
            $content = $this->getContentForStub($file['stub'], $this->moduleName);
            file_put_contents("$this->modulePath/" . $file['destination'], $content);
        }
    }
}
