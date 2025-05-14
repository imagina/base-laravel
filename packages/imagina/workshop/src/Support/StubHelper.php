<?php

namespace Imagina\Workshop\Support;

use Exception;
use Illuminate\Support\Str;

trait StubHelper
{
    /**
     * @throws Exception
     */
    protected function getContentForStub(string $stubName, string $class = '', string $package = '', string $entityType = 'entity'): string
    {
        $stubPath = __DIR__ . "/../stubs/$stubName.stub";
        if (!file_exists($stubPath)) {
            throw new Exception("Stub not found: $stubPath");
        }

        $stub = file_get_contents($stubPath);

        return str_replace(
            [
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
                $package,
                strtolower($package),
                strtolower(Str::plural($package)),
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
