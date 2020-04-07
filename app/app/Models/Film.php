<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Film extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'imdb_id',
        'title',
        'release_date',
        'rating',
        'category',
        'director',
    ];
}
