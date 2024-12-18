<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoetryCollection extends Model
{
    use HasFactory, SoftDeletes;
// poetry_collection
    protected $fillable = [
        'poet_id',
        'title',
        'description',
        'poems_count',
        'status',
        'publish_status',
    ];

    protected $casts = [
        'title'             => 'string',
        'description'       => 'string',
        'poems_count'       => 'integer',
        'status'            => 'boolean',
        'publish_status'    => 'boolean',
    ];

    public function poet(){
        return $this->belongsTo(User::class);
    }
}
