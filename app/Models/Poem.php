<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'user_id',
        'title',
        'poet_id',
        'type',
        'poem_type_id',
        'category_id',
        'language_id',
        'occasion_id',
        'body',
        'audios_count',
        'status',
    ];

    protected $casts = [
        'created_by'    => 'string',
        'title'         => 'string',
        'body'          => 'string',
        'audios_count'  => 'integer',
        'status'        => 'boolean',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function poet(){
        return $this->belongsTo(User::class);
    }

    public function poem_type(){
        return $this->belongsTo(PoemType::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function language(){
        return $this->belongsTo(Language::class);
    }

    public function occasion(){
        return $this->belongsTo(Occasion::class);
    }

    public function audios()
    {
        return $this->hasMany(Audio::class);
    }
}
