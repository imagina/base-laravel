<?php

namespace Imagina\Iworkshop\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Imagina\Iworkshop\Support\ScaffoldTrait;

class MakeEntityCommand extends Command
{
    use ScaffoldTrait;

    protected $signature = 'module:scaffold:entity {moduleScaffolding?} {entityCreation?}';
    protected $description = 'Create model to an existing module | {module?} {entity?}';
    protected $entityFiles = [];

    public function handle()
    {
        $this->getEntityName($this->ARG_ENTITY_CREATION);
        $this->generateFiles([
            [
                'stub' => '4-entity-eloquent',
                'destination' => $this->appFolderPath . "/Models/{$this->entityName}.php"
            ],
            [
                'stub' => '4-eloquent-entity-translation',
                'destination' => $this->appFolderPath . "/Models/{$this->entityName}Translation.php"
            ],
            [
                'stub' => '5-api-controller',
                'destination' => $this->appFolderPath . "/Http/Controllers/Api/{$this->entityName}ApiController.php"
            ],
            [
                'stub' => '8-repository-interface',
                'destination' => $this->appFolderPath . "Repositories/{$this->entityName}Repository.php"
            ],
            [
                'stub' => '8-cache-repository-decorator',
                'destination' => $this->appFolderPath . "Repositories/Cache/Cache{$this->entityName}Decorator.php"
            ],
            [
                'stub' => '8-eloquent-repository',
                'destination' => $this->appFolderPath . "Repositories/Eloquent/Eloquent{$this->entityName}Repository.php"
            ],
            [
                'stub' => '9-transformer',
                'destination' => $this->appFolderPath . "Transformers/{$this->entityName}Transformer.php"
            ],
            ...$this->getMigrationFiles()
        ]);
        $this->appendStub('2-permissions-append', 'config/permissions.php');
        $this->appendStub('6-route-resource-api', 'routes/api.php');
        $this->appendStub('7-bindings', $this->appFolderPath . "Providers/" . $this->moduleName . "ServiceProvider.php", '// add bindings');
        $this->info("Entity $this->entityName successfully scaffolded in package $this->moduleName.");
    }

    protected function getMigrationFiles(): array
    {
        $lowercaseModule = strtolower($this->moduleName);
        $pluralEntity = strtolower(Str::plural($this->entityName));
        $singularEntity = strtolower($this->entityName);

        // Get base timestamp and increment for the second migration
        $timestamp = now();
        $migrationName1 = $timestamp->format('Y_m_d_His') . "_create_{$lowercaseModule}_{$pluralEntity}_table.php";
        $timestamp->addSecond();
        $migrationName2 = $timestamp->format('Y_m_d_His') . "_create_{$lowercaseModule}_{$singularEntity}_translations_table.php";

        // return files
        return [
            ['stub' => '3-create-table-migration', 'destination' => "database/Migrations/{$migrationName1}"],
            ['stub' => '3-create-translation-table-migration', 'destination' => "database/Migrations/{$migrationName2}"]
        ];
    }
}
