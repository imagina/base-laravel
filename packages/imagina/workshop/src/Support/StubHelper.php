<?php

namespace Imagina\Workshop\Support;

use Exception;
use Illuminate\Support\Str;

trait StubHelper
{
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
