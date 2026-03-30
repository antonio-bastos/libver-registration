<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'storage_path',
        'mime',
        'size',
        'type',
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'size' => 'integer',
    ];
}
