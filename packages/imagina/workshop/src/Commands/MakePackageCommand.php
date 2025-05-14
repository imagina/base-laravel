<?php

namespace Imagina\Workshop\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Imagina\Workshop\Support\StubHelper;

class MakePackageCommand extends Command
{
    use StubHelper;

    protected $signature = 'workshop:make-package {name}';
    protected $description = 'Create a new base Laravel package with DDD structure';

    public function handle(): void
    {
        $name = Str::studly($this->argument('name'));
        $packagePath = base_path("packages/imagina/$name");
        $srcPath = "$packagePath/src";

        if (file_exists($packagePath)) {
            $this->error("Package already exists: $name");
            return;
        }

        // Define folders
        $folders = [
            'Config',
            'Database/Factories',
            'Database/Migrations',
            'Database/Seeders',
            'Entities',
            'Http/Controllers',
            'Http/Middleware',
            'Http/routes',
            'Providers',
            'Repositories/Eloquent',
            'Repositories/Cache',
            'Transformers',
        ];

        // Create folder structure
        foreach ($folders as $folder) {
            mkdir("$srcPath/$folder", 0755, true);
        }

        // Generate files using stubs
        $this->makeComposerJson($packagePath, $name);
        $this->makeConfig($srcPath, $name);
        $this->makePermissions($srcPath, $name);
        $this->makeRoutes($srcPath, $name);
        $this->makeServiceProvider($srcPath, $name);

        $this->info("Package $name created successfully at packages/imagina/$name");
    }

    protected function makeComposerJson(string $basePath, string $name): void
    {
        $composerJson = [
            "name" => "imagina/$name",
            "autoload" => [
                "psr-4" => [
                    "Imagina\\$name\\" => "src/"
                ]
            ]
        ];

        file_put_contents(
            "$basePath/composer.json",
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function makeConfig(string $srcPath, string $name): void
    {
        $content = $this->getContentForStub('1-config', '', $name);
        file_put_contents("$srcPath/Config/config.php", $content);
    }

    protected function makePermissions(string $srcPath, string $name): void
    {
        $content = $this->getContentForStub('2-permissions', '', $name);
        file_put_contents("$srcPath/Config/permissions.php", $content);
    }

    protected function makeRoutes(string $srcPath, string $name): void
    {
        $content = $this->getContentForStub('6-route-resource-api', '', $name);
        file_put_contents("$srcPath/Http/routes/web.php", $content);
    }

    protected function makeServiceProvider(string $srcPath, string $name): void
    {
        $content = $this->getContentForStub('7-module-service-provider', 'ModuleServiceProvider', $name);
        file_put_contents("$srcPath/Providers/ModuleServiceProvider.php", $content);
    }
}
