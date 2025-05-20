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
    public string $entityName = '';
    public string $entityPath = '';

    public string $ARG_MODULE_CREATION = 'moduleCreation';
    public string $ARG_MODULE_SCAFFOLDING = 'moduleScaffolding';
    public string $ARG_ENTITY_CREATION = 'entityCreation';

    protected function getModuleName(string $argument): string
    {
        $this->laravelModulesPath = config('modules.paths.modules', base_path('Modules'));
        if (!$this->moduleName) $this->moduleName = $this->getArgument($argument, 'Please enter the name of the module');
        $this->modulePath = $this->getModulePath($this->moduleName);
        return $this->moduleName;
    }

    protected function getEntityName(string $argument): string
    {
        $this->getModuleName($this->ARG_MODULE_SCAFFOLDING);
        $this->entityName = $this->getArgument($argument, 'Please enter the name of the entity');
        $this->entityPath = $this->getEntityPath($this->entityName);
        return $this->entityName;
    }

    protected function getArgument(string $argument, string $description): string
    {
        $value = Str::studly(trim((string)$this->argument($argument)));
        if ($value) {
            $isInvalid = $this->validateArg($argument, $value);
            if ($isInvalid) {
                if ($isInvalid) $this->warn("⚠ $isInvalid");;
                $value = null;
            }
        }
        if (!$value) $value = Str::studly(text(
            label: $description,
            required: true,
            validate: fn(string $value) => $this->validateArg($argument, $value)
        ));
        return $value;
    }

    protected function getModulePath(string $moduleName): string
    {
        return "{$this->laravelModulesPath}/$moduleName";
    }

    protected function getEntityPath(string $entityName): string
    {
        return "{$this->modulePath}/" . config('modules.paths.app_folder') . "Models/$entityName.php";
    }

    protected function validateArg(string $argument, string $value): ?string
    {
        $isInvalid = null;
        $allowedArg = [$this->ARG_MODULE_CREATION, $this->ARG_MODULE_SCAFFOLDING, $this->ARG_ENTITY_CREATION];
        if (!in_array($argument, $allowedArg, true)) $isInvalid = 'Invalid Argument.';

        if (strlen($value) < 3) $isInvalid = 'The argument must be at least 3 characters.';

        if (Str::contains($value, ' ')) $isInvalid = 'The argument must not contain spaces.';

        if (!$isInvalid) {
            $modulePath = $this->getModulePath($value);
            $entityPath = $this->getEntityPath($value);
            $isInvalid = match ($argument) {
                $this->ARG_MODULE_CREATION => file_exists($modulePath) ? "Module $value already exists." : null,
                $this->ARG_MODULE_SCAFFOLDING => !file_exists($modulePath) ? "Module $value doesn't exist." : null,
                $this->ARG_ENTITY_CREATION => file_exists($entityPath) ? "Entity $value already exists." : null,
                default => null,
            };
        }
        return $isInvalid;
    }

    protected function getContentForStub(string $stubName): string
    {
        $stubPath = __DIR__ . "/../../stubs/$stubName.stub";
        $moduleName = $this->moduleName;
        $entityName = $this->entityName;
        if (!file_exists($stubPath)) throw new Exception("Stub not found: $stubPath");
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
                $entityName,
                strtolower($entityName),
                strtolower(Str::plural($entityName)),
                Str::plural($entityName),
                'Eloquent',
            ],
            $stub
        );
    }
}
