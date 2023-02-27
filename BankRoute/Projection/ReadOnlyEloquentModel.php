<?php

declare(strict_types=1);

namespace BankRoute\Projection;

use LogicException;
use Illuminate\Database\Eloquent\Model;

abstract class ReadOnlyEloquentModel extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['*'];

    protected $fillable = [];

    public function save(array $options = []): bool
    {
        throw new LogicException('Model is read only');
    }
}
