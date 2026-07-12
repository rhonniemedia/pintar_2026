<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoreConcentration extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'core_concentrations';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];
}
