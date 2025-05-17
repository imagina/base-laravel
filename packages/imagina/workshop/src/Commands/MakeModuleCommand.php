<?php

namespace Imagina\Workshop\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Imagina\Workshop\Support\StubHelper;
use function Laravel\Prompts\text;

class MakeModuleCommand extends Command
{
    use StubHelper;

    protected $signature = 'module:scaffold';
    protected $description = 'Create a laravel-module';

    protected string $laravelModulesPath = '';
    protected string $moduleName = '';
    protected string $modulePath = '';


    public function handle(): void
    {
        $this->laravelModulesPath = config('modules.paths.modules', base_path('Modules'));
        $this->moduleName = $this->getModuleName();
        $this->modulePath = "$this->laravelModulesPath/$this->moduleName";

        $this->createFolderStructure();
        $this->createInitialFilesFromStubs();

        $this->info("Package $this->moduleName created successfully at packages/imagina/$this->moduleName");
    }

    protected function getModuleName(): string
    {
        return ucwords(
            text(
                label: 'Please enter a name for the module to be created',
                required: true,
                validate: fn(string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
                    file_exists("$this->laravelModulesPath/$value") => 'Module already exists.',
                    default => null
                }
            )
        );
    }

    protected function createFolderStructure(): void
    {
        $folders = [
            'config',
            'app/Models',
            'app/Http/Controllers',
            'app/Http/Routes',
            'app/Http/Transformers',
            'app/Providers',
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
            ['stub' => '6-route-resource-api', 'destination' => 'app/Http/routes/web.php'],
            ['stub' => '7-module-service-provider', 'destination' => 'app/Providers/ModuleServiceProvider.php'],
        ];

        foreach ($files as $file) {
            $content = $this->getContentForStub($file['stub'], $this->moduleName);
            file_put_contents("$this->modulePath/" . $file['destination'], $content);
        }
    }
}
