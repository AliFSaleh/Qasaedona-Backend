<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'poet_id',
        'title',
        'body',
        'status',
        'publish_status',
    ];

    protected $casts = [
        'title'           => 'string',
        'body'            => 'string',
        'status'          => 'boolean',
        'publish_status'  => 'boolean',
    ];

    
}
