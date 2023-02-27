<?php

declare(strict_types=1);

namespace App\Console\Commands\Generator;

use Illuminate\Filesystem\Filesystem;

class MakeQueryProjectionCommand extends ProjectionGeneratorCommand
{
    protected $signature = 'make:projection-query
                            { name              : The name of the class }
                            { projection-from   : Available from are: all, category, stream }
                            { --stream=*        : The names of the streams/categories, *required* for "projection-from" Streams/Categories, default TODO }';

    protected $description = 'Make a new persistent projection';

    protected Filesystem $files;

    protected function getProjectionType(): string
    {
        return 'query';
    }
}
