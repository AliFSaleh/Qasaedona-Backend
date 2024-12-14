<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occasion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'image',
        'date',
        'type',
        'status',
        'poems_count',
    ];

    protected $casts = [
        'title'         => 'string',
        'image'         => 'string',
        'date'          => 'string',
        'type'          => 'string',
        'status'        => 'boolean',
        'poems_count'   => 'integer',
    ];
}
