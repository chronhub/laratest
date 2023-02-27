<?php

declare(strict_types=1);

namespace App\Console\Commands\Generator;

use Illuminate\Filesystem\Filesystem;
use function array_merge;

class MakePersistentProjectionCommand extends ProjectionGeneratorCommand
{
    protected $signature = 'make:projection-persistent
                            { name              : The name of the class }
                            { projection-name   : The name of the projection }
                            { projection-from   : Available from are: all, category, stream }
                            { --stream=*        : The names of the streams/categories, *required* for "projection-from" Streams/Categories, default projection name }';

    protected $description = 'Make a new persistent projection';

    protected Filesystem $files;

    protected function getStubVariables(): array
    {
        $defaultStubs = parent::getStubVariables();

        return array_merge($defaultStubs, [
            'PROJECTION_NAME' => $this->argument('projection-name'),
        ]);
    }

    protected function getProjectionType(): string
    {
        return 'projection';
    }
}
