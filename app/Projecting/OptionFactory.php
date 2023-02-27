<?php

declare(strict_types=1);

namespace App\Projecting;

use Str;
use Illuminate\Support\Collection;
use Chronhub\Storm\Contracts\Projector\ProjectorOption;
use Chronhub\Storm\Projector\Options\DefaultProjectorOption;
use function is_int;
use function is_bool;
use function is_array;

final class OptionFactory
{
    private Collection $options;

    public function __construct(ProjectorOption|array $option = [])
    {
        if (is_array($option)) {
            $option = new DefaultProjectorOption(...$option);
        }

        $this->options = new Collection($option);
    }

    public function toOptionsString(): string
    {
        return $this->options
            ->map(fn ($value, $key) => $this->formatOption($key, $value))
            ->implode(' ');
    }

    private function formatOption(string $key, null|bool|int|array $value): string
    {
        $key = '--'.Str::kebab($key);

        if ($value === null || (is_array($value) && empty($value))) {
            return "$key=";
        }

        if (is_bool($value)) {
            return $value ? $key.'=1' : $key.'=0';
        }

        if (is_int($value)) {
            return "$key=$value";
        }

        $value = collect($value)
            ->map(fn ($value) => $value)
            ->implode(',');

        return "$key='$value'";
    }
}
