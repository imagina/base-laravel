<?php

namespace Imagina\Workshop\Support;

use Exception;
use Illuminate\Support\Str;
use function Laravel\Prompts\text;

trait ScaffoldTrait
{
    public string $laravelModulesPath = '';
    public string $modulePath = '';
    public string $moduleName = '';
    public string $modelName = '';
    public string $modelPath = '';

    protected function getModuleName(string $type): string
    {
        $this->laravelModulesPath = config('modules.paths.modules', base_path('Modules'));

        $moduleName = Str::studly($this->argument('module'));
        if ($moduleName) {
            $isInvalid = $this->validateModuleName($moduleName, $type);
            if (!$isInvalid) return $moduleName;
            $this->warn("⚠ $isInvalid");
        }
        $moduleName = Str::studly(text(
            label: 'Please enter a name for the module to be created',
            required: true,
            validate: fn(string $value) => $this->validateModuleName($value, $type)
        ));

        $this->moduleName = $moduleName;
        $this->modulePath = "$this->laravelModulesPath/$moduleName";
        return $moduleName;
    }

    protected function getModelName(): string
    {
        $this->laravelModulesPath = config('modules.paths.modules', base_path('Modules'));

        $modelName = Str::studly($this->argument('model'));
        if ($modelName) {
            $isInvalid = $this->validateModuleName($modelName, $type);
            if (!$isInvalid) return $modelName;
            $this->warn("⚠ $isInvalid");
        }
        $modelName = Str::studly(text(
            label: 'Please enter a name for the module to be created',
            required: true,
            validate: fn(string $value) => $this->validateModuleName($value, $type)
        ));

        $this->moduleName = $modelName;
        $this->modulePath = "$this->laravelModulesPath/$modelName";
        return $modelName;
    }

    protected function validateModuleName(string $value, string $type): ?string
    {
        // Allowed types
        $allowedTypes = ['creating', 'scaffolding'];
        if (!in_array($type, $allowedTypes, true)) return 'Invalid validation type.';

        if (strlen($value) < 3) return 'The name must be at least 3 characters.';

        if (Str::contains($value, ' ')) return 'The name must not contain spaces.';

        $modulePath = "{$this->laravelModulesPath}/$value";
        return match ($type) {
            'creating' => file_exists($modulePath) ? 'Module already exists.' : null,
            'scaffolding' => !file_exists($modulePath) ? "Module doesn't exist." : null,
            default => null,
        };
    }

    protected function getContentForStub(string $stubName, string $moduleName = '', string $class = '', string $entityType = 'entity'): string
    {
        $stubPath = __DIR__ . "/../../stubs/$stubName.stub";
        if (!file_exists($stubPath)) {
            throw new Exception("Stub not found: $stubPath");
        }

        $stub = file_get_contents($stubPath);

        return str_replace(
            [
                '$MODULE_NAMESPACE$',
                '$APP_FOLDER_NAME$',
                '$VENDOR$',
                '$AUTHOR_NAME$',
                '$AUTHOR_EMAIL$',
                '$MODULE_NAME$',
                '$LOWERCASE_MODULE_NAME$',
                '$PLURAL_LOWERCASE_MODULE_NAME$',
                '$CLASS_NAME$',
                '$LOWERCASE_CLASS_NAME$',
                '$PLURAL_LOWERCASE_CLASS_NAME$',
                '$PLURAL_CLASS_NAME$',
                '$ENTITY_TYPE$',
            ],
            [
                config('modules.namespace'),
                config('modules.paths.app_folder'),
                config('modules.composer.vendor'),
                config('modules.composer.author.name'),
                config('modules.composer.author.email'),
                $moduleName,
                strtolower($moduleName),
                strtolower(Str::plural($moduleName)),
                $class,
                strtolower($class),
                strtolower(Str::plural($class)),
                Str::plural($class),
                $entityType,
            ],
            $stub
        );
    }
}
