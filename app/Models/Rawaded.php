<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rawaded extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'image',
        'audio_count',
        'featured',
        'status',
    ];

    protected $casts = [
        'name'          => 'string',
        'image'         => 'string',
        'audio_count'   => 'integer',
        'featured'      => 'boolean',
        'status'        => 'boolean',
    ];
}
