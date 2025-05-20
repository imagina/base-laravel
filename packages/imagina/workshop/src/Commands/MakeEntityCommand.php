<?php

namespace Imagina\Workshop\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Imagina\Workshop\Support\ScaffoldTrait;

class MakeEntityCommand extends Command
{
    use ScaffoldTrait;

    protected $signature = 'module:scaffold:entity {moduleScaffolding?} {entityCreation?}';
    protected $description = 'Add a model and its components to an existing package';

    public function handle()
    {
        $this->getEntityName($this->ARG_ENTITY_CREATION);
        $this->appendPermissions();
        $this->generateMigrations();
        $this->generateEntities();
        $this->generateController();
        $this->generateRepository();
        $this->bindRepository();
        $this->generateTranslation();

        $this->info("Entity $this->entityName successfully scaffolded in package $this->moduleName.");
    }

    protected function appendPermissions()
    {
        $configPath = "$this->modulePath/config/config.php";
        if (!file_exists($configPath)) return;
        $append = $this->getContentForStub('2-permissions-append');
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

    protected function generateMigrations(): void
    {
        usleep(250000); // Ensure unique timestamps between migrations

        $lowercaseModule = strtolower($this->moduleName);
        $pluralEntity = strtolower(Str::plural($this->entityName));
        $singularEntity = strtolower($this->entityName);
        $timestamp1 = now()->format('Y_m_d_His');

        $migrationName1 = "{$timestamp1}_create_{$lowercaseModule}_{$pluralEntity}_table.php";
        $content1 = $this->getContentForStub('3-create-table-migration');
        file_put_contents("$this->modulePath/Database/Migrations/{$migrationName1}", $content1);

        usleep(250000); // Space out timestamps

        $timestamp2 = now()->addSeconds(1)->format('Y_m_d_His');
        $migrationName2 = "{$timestamp2}_create_{$lowercaseModule}_{$singularEntity}_translations_table.php";
        $content2 = $this->getContentForStub('3-create-translation-table-migration');
        file_put_contents("$this->modulePath/database/Migrations/{$migrationName2}", $content2);
    }

    protected function generateEntities(): void
    {
        $content = $this->getContentForStub('4-entity-eloquent');
        file_put_contents("$this->modulePath/App/Models/{$this->entityName}.php", $content);
    }

    protected function generateController()
    {
        $content = $this->getContentForStub('5-api-controller');
        file_put_contents("$this->modulePath/App/Http/Controllers/{$this->entityName}Controller.php", $content);
    }

    protected function generateRepository()
    {
        $interface = $this->getContentForStub('8-repository-interface');
        $implementation = $this->getContentForStub('8-eloquent-repository');

        file_put_contents("$this->modulePath/app/Repositories/{$this->entityName}RepositoryInterface.php", $interface);
        file_put_contents("$this->modulePath/app/Repositories/{$this->entityName}Repository.php", $implementation);
    }

    protected function bindRepository()
    {
        $providerPath = "$this->modulePath/Providers/ModuleServiceProvider.php";
        if (!file_exists($providerPath)) return;

        $binding = $this->getContentForStub('7-bindings');
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

    protected function generateTranslation()
    {
        $dir = "$this->modulePath/Entities";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $content = $this->getContentForStub('4-eloquent-entity-translation');
        file_put_contents("$dir/{$this->entityName}Translation.php", $content);
    }
}
