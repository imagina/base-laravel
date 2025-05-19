<?php

namespace Imagina\Workshop\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Imagina\Workshop\Support\ScaffoldTrait;

class MakeModelCommand extends Command
{
    use ScaffoldTrait;

    protected $signature = 'module:scaffold:model {module?} {model?}';
    protected $description = 'Add a model and its components to an existing package';

    public function handle()
    {
        $this->laravelModulesPath = config('modules.paths.modules', base_path('Modules'));
        $this->moduleName = $this->getModuleName('scaffolding');
        $this->modulePath = "$this->laravelModulesPath/$this->moduleName";



        $package = Str::studly($this->argument('package'));
        $model = Str::studly($this->argument('model'));
        $basePath = base_path("packages/imagina/{$package}/src");

        if (!is_dir($basePath)) {
            $this->error("Package [$package] does not exist.");
            return;
        }

        $this->appendPermissions($basePath, $model);
        $this->generateMigrations($basePath, $package, $model);
        $this->generateEntities($basePath, $package, $model);
        $this->generateController($basePath, $package, $model);
        $this->generateRepository($basePath, $package, $model);
        $this->generateTranslation($basePath, $package, $model);
        $this->bindRepository($basePath, $package, $model);

        $this->info("Model $model successfully scaffolded in package $package.");
    }

    protected function generateMigrations(string $basePath, string $package, string $model): void
    {
        usleep(250000); // Ensure unique timestamps between migrations

        $lowercaseModule = strtolower($package);
        $pluralEntity = strtolower(Str::plural($model));
        $singularEntity = strtolower($model);

        $timestamp1 = now()->format('Y_m_d_His');
        $migrationName1 = "{$timestamp1}_create_{$lowercaseModule}_{$pluralEntity}_table.php";
        $content1 = $this->getContentForStub('3-create-table-migration', $model, $package);
        file_put_contents("$basePath/Database/Migrations/{$migrationName1}", $content1);

        usleep(250000); // Space out timestamps

        $timestamp2 = now()->addSeconds(1)->format('Y_m_d_His');
        $migrationName2 = "{$timestamp2}_create_{$lowercaseModule}_{$singularEntity}_translations_table.php";
        $content2 = $this->getContentForStub('3-create-translation-table-migration', $model, $package);
        file_put_contents("$basePath/Database/Migrations/{$migrationName2}", $content2);
    }

    protected function generateEntities(string $basePath, string $package, string $model): void
    {
        $content = $this->getContentForStub('4-entity-eloquent', $model, $package);
        file_put_contents("$basePath/Entities/{$model}.php", $content);
    }

    protected function generateTranslation(string $basePath, string $package, string $model)
    {
        $dir = "$basePath/Entities";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $content = $this->getContentForStub('4-eloquent-entity-translation', $model, $package);
        file_put_contents("$dir/{$model}Translation.php", $content);
    }

    protected function generateRepository(string $basePath, string $package, string $model)
    {
        $interface = $this->getContentForStub('8-repository-interface', $model, $package);
        $implementation = $this->getContentForStub('8-eloquent-repository', $model, $package);

        file_put_contents("$basePath/Repositories/{$model}RepositoryInterface.php", $interface);
        file_put_contents("$basePath/Repositories/{$model}Repository.php", $implementation);
    }

    protected function generateController(string $basePath, string $package, string $model)
    {
        $content = $this->getContentForStub('5-api-controller', $model, $package);
        file_put_contents("$basePath/Http/Controllers/{$model}Controller.php", $content);
    }

    protected function appendPermissions(string $basePath, string $model)
    {
        $configPath = "$basePath/Config/config.php";
        if (!file_exists($configPath)) return;

        $append = $this->getContentForStub('2-permissions-append', $model, '', '');
        $content = file_get_contents($configPath);

        if (strpos($content, "'permissions' => [") !== false) {
            $content = preg_replace(
                "/('permissions'\\s*=>\\s*\\[)/",
                "$1\n        $append,",
                $content
            );
            file_put_contents($configPath, $content);
        }
    }

    protected function bindRepository(string $basePath, string $package, string $model)
    {
        $providerPath = "$basePath/Providers/ModuleServiceProvider.php";
        if (!file_exists($providerPath)) return;

        $binding = $this->getContentForStub('7-bindings', $model, $package);
        $content = file_get_contents($providerPath);

        if (strpos($content, $binding) === false && preg_match('/public function register\\(\\)\\s*\\{/', $content)) {
            $content = preg_replace(
                '/public function register\\(\\)\\s*\\{/',
                "public function register()\n    {\n        $binding",
                $content
            );
            file_put_contents($providerPath, $content);
        }
    }
}
