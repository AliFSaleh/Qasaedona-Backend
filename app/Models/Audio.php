<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Audio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'poem_id',
        'title',
        'audio_type',
        'link',
    ];

    protected $casts = [
        'audio_type' => 'string',
        'title'      => 'string',
        'link'       => 'string',
    ];

    public function poem(){
        return $this->belongsTo(Poem::class);
    }
}
