<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class History extends Model
{
    use HasUuids;

    protected $table = 'historys';

    protected $guarded = []; // History::create([])

    protected $casts = [
        'parts' => 'array'
    ];
}
