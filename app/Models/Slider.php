<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'linked_type',
        'linked_id',
        'type',
        'external_link',
    ];

    public function linked(): MorphTo
    {
        return $this->morphTo();
    }

}
